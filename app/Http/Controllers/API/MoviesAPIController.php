<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Log;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\UserMovies;
use Validator;


class MoviesAPIController extends Controller
{
    //protected $redirectTo = '/movies';

    public $successStatus = 200;
    
    /**
    * login getUserMovies
    *
    * @return \Illuminate\Http\Response
    */
    public function getUserMovies(Request $request)
    {
        Log::info('in MoviesAPIController getUserMovies... ');

        
        if(Auth::check())
        {
            Log::info('in MoviesAPIController getUserMovies - Auth passed');

            $user = Auth::user();

            $tableInfo = array();

            $tableInfo['rating'] = 'user_movies';
            $tableInfo['name'] = 'formats';
            $tableInfo['lengthTotal'] = 'movies';
            $tableInfo['title'] = 'movies';
            $tableInfo['releaseYear'] = 'movies';

            if(!is_null(request('orderBy')) && !is_null(request('direction')) && request('direction') != 'none')
            {                
                $orderBy = $tableInfo[request('orderBy')].".".request('orderBy');
                $direction = request('direction');
            }
            else
            {
                $orderBy = 'movies.id';
                $direction = 'asc';
            }
            
            $success['userMovies'] = DB::table('user_movies')
            ->join('users', 'users.id', '=', 'user_movies.userId')
            ->join('movies', 'movies.id', '=', 'user_movies.movieId')
            ->join('formats', 'formats.id', '=', 'user_movies.formatId')
            ->select('movies.id', 'users.firstName', 'users.lastName','movies.id', 'movies.title', 'movies.lengthTotal' , 'movies.releaseYear', 'user_movies.rating', 'user_movies.formatId')
            ->where('users.id', '=', $user->id)
            ->where('user_movies.active', '>', 0)
            ->where('movies.active', '>', 0)
            ->orderBy($orderBy, $direction)
            ->get();
            
            $success['sorting'] = array('orderBy' => substr($orderBy, strpos($orderBy, '.')+1, strlen($orderBy)) , 'direction' => $direction);
            
            return response()->json(['success' => $success], $this->successStatus);                
        }
        else
        {
            return response()->json(['error'=>'Unauthorised'], 401);
        }
    }


    /**
        * createUserMovie api
        *
        * @return \Illuminate\Http\Response
        */
    public function createUserMovie(Request $request)
    {
        Log::info('in MoviesAPIController createUserMovie... ');

        
        if(Auth::check())
        {
            Log::info('in MoviesAPIController createUserMovie - Auth passed');                
            
            $validator = Validator::make($request->all(), 
            [
                'title' => 'required|max:50',                
                'lengthTotal' => 'required|integer|between:1,500',                
                'year' => 'required|integer|between:1801,2099',                                
                'formatId' => 'required|integer|min:1|max:3',                                    
                'rating' => 'sometimes|integer|between:1,5',                                    
            ]);                

            if ($validator->fails()) 
            {
                Log::info('error in MoviesAPIController createUserMovie - '. $validator->errors());
                return response()->json(['error'=>$validator->errors()], 500);            
            }

            DB::beginTransaction();
                            
            $timestamp = date("Y-m-d H:i:s");
            $user = Auth::user();
            $foundErrors = array();

            try 
            {   
                Log::info('now create movies in MoviesAPIController createUserMovie - '); 
                $movidId = DB::table('movies')->insertGetId(
                    ['title' => request('title'), 
                    'lengthTotal' => request('lengthTotal'), 
                    'releaseYear' => request('year'),                     
                    'createdDate' => $timestamp, 
                    'createdBy' => $user->id,                     
                    'active' => 1]
                );

                if(is_null($movidId) || !is_int($movidId) || $movidId <=0)
                {
                    DB::rollback();
                    $foundErrors[] = 'Failed to create movie.';
                    Log::info('error in MoviesAPIController createUserMovie movie insert - Failed to create movie.');
                }
            } 
            catch(ValidationException $e)
            {  
                DB::rollback();
                $foundErrors[] =  $e->getErrors();  //get error to send back with response
                Log::info('ValidationException in MoviesAPIController createUserMovie movies insert - '. $e->getErrors());
            }
            catch(\Exception $e)
            {
                Log::info('Exception in MoviesAPIController createUserMovie movies insert - e');
                DB::rollback();
                throw $e;
            }

            Log::info('done with movies in MoviesAPIController createUserMovie movidId - '.$movidId);

            if (empty($foundErrors)) 
            {
                Log::info('now create user_movies in MoviesAPIController createUserMovie - ');
                try 
                { 
                    $userMovieId = DB::table('user_movies')->insertGetId(
                        ['userId' => $user->id, 
                        'movieId' => $movidId, 
                        'rating' => request('rating'), 
                        'formatId' => request('formatId'), 
                        'createdDate' => $timestamp, 
                        'createdBy' => $user->id,                     
                        'active' => 1]
                    );

                    //Getting $userMovieId == 0 ...might be part of transaction...so not checking this
                    // if(is_null($userMovieId) || !is_int($userMovieId) || $userMovieId <=0)
                    // {
                    //     DB::rollback();

                    //     $foundErrors[] = 'Failed to create user_movie.'; //get error to send back with response
                    //     Log::info('error in MoviesAPIController createUserMovie user_movies insert - Failed to create user_movies. userMovieId='.$userMovieId);
                    
                    // }
                } 
                catch(ValidationException $e)
                {                            
                    DB::rollback();
                    $foundErrors[] =  $e->getErrors();  //get error to send back with response
                    Log::info('ValidationException in MoviesAPIController createUserMovie user_movies insert - '. $e->errors());
                }
                catch(\Exception $e)
                {
                    Log::info('Exception in MoviesAPIController createUserMovie user_movies insert - e');
                    DB::rollback();
                    throw $e;
                }
            }

            Log::info('done with movies in MoviesAPIController createUserMovie - ');
            if (empty($foundErrors)) 
            {
                DB::commit();
                $success['userMovie'] = json_encode(array('$movidId' => $movidId, 'title' => request('title'), 'lengthc' => request('lengthTotal'), 'year' => request('year'), 'rating' => request('rating')));
                return response()->json(['success' => $success], $this->successStatus);
            }
            else
            {
                //put DB::rollback(); everywhere an exception is found, in case we dont get to this spot
                return response()->json(['errors'=>$foundErrors], 500);                      
            }                
        }
        else
        {
            return response()->json(['error'=>'Unauthorised'], 401);
        }
    }


    /**
    * createUserMovie api
    *
    * @return \Illuminate\Http\Response
    */
    public function updateUserMovie(Request $request, $id)
    {
        //since we are using the auth::user id and the passed in movie id on the user_movie table, 
        //we should be protecting other user's data (if somone passes in a movie id that is not part of the user_movie PK pair)
        Log::info('in MoviesAPIController updateUserMovie... ');
        Log::info('in MoviesAPIController updateUserMovie year = '.request('year'));
        DB::enableQueryLog();    
        
        if(Auth::check())
        {
            Log::info('in MoviesAPIController updateUserMovie - Auth passed');                
            Log::info('in movieId = '. $id);
            
            Log::info(request()->all());
               
                $validator = Validator::make($request->all(), 
                [
                    //'movieId' => 'required|integer|min:1',
                    
                    'title' => 'sometimes|max:50',                
                    'lengthTotal' => 'sometimes|integer|between:1,500',
                    'year' => 'sometimes|integer|between:1801,2099',                               
                    'formatId' => 'sometimes|integer|min:1|max:3', 
                    'rating' => 'sometimes|integer|between:1,5',            
                ]);                

            if ($validator->fails()) 
            {
                Log::info('error in MoviesAPIController updateUserMovie - '. $validator->errors());
                return response()->json(['error'=>$validator->errors()], 500);            
            }

            $rowsAffected = 0;
            DB::beginTransaction();
                            
                $timestamp = date("Y-m-d H:i:s");
                $user = Auth::user();
                $foundErrors = array();
                
                Log::info('in userId = '.$user->id);
                try 
                {   
                    Log::info('now update movies in MoviesAPIController updateUserMovie - ');   

                    
                    $updateParams = array(); 
                    $request->has('formatId') ? $updateParams['formatId'] = request('formatId') : 1;
                    $request->has('rating') ? $updateParams['rating'] = request('rating') : 1;
                    $updateParams['modifiedDate'] = $timestamp;
                    $updateParams['modifiedBy'] = $user->id;
                   
                    Log::info($updateParams);

                    $rowsAffected = DB::table('user_movies')                
                                    ->where('userId', '=', $user->id)                
                                    ->where('movieId','=', $id)
                                    ->update($updateParams);
                        
                    Log::info(DB::getQueryLog());
                    Log::info('after update user_movies... rows affected = '.$rowsAffected);
                } 
                catch(ValidationException $e)
                {  
                    DB::rollback();
                    $foundErrors[] =  $e->getErrors();  //get error to send back with response
                    Log::info('ValidationException in MoviesAPIController updateUserMovie user_movies update - '. $e->getErrors());
                }
                catch(\Exception $e)
                {
                    Log::info('Exception in MoviesAPIController updateUserMovie user_movies update - e');
                    DB::rollback();
                    throw $e;
                }

                Log::info('done with user_movies in MoviesAPIController updateUserMovie ');

                if($rowsAffected == 0 )
                {
                    $foundErrors[] = "Failed to update user_movies!";
                }                
                
                if (empty($foundErrors)) 
                {
                    Log::info('now update movies in MoviesAPIController updateUserMovie - ');

                    $rowsAffected = 0;
                    try 
                    {

                        $updateParams = array(); 
                        $request->has('title') ? $updateParams['title'] = request('title') : 1;
                        $request->has('lengthTotal') ? $updateParams['lengthTotal'] = request('lengthTotal') : 1;
                        $request->has('year') ? $updateParams['releaseYear'] = request('year') : 1;
                        $updateParams['modifiedDate'] = $timestamp;
                        $updateParams['modifiedBy'] = $user->id;

                        Log::info($updateParams);

                        $rowsAffected = DB::table('movies')
                                        ->where('id', $id)
                                        ->update($updateParams);
                        
                        Log::info(DB::getQueryLog());
                        Log::info('after update movies... rows affected = '.$rowsAffected);
                    } catch(ValidationException $e)
                    {                            
                        DB::rollback();
                        $foundErrors[] =  $e->getErrors();  //get error to send back with response
                        Log::info('ValidationException in MoviesAPIController updateUserMovie movies update - '. $e->errors());
                    }
                    catch(\Exception $e)
                    {
                        Log::info('Exception in MoviesAPIController updateUserMovie movies update - e');
                        DB::rollback();
                        throw $e;
                    }
                }

            Log::info('done with movies in MoviesAPIController updateUserMovie - ');

            if($rowsAffected == 0 )
            {
                $foundErrors[] = "Failed to update movies!";
                DB::rollback();
            }      

            if (empty($foundErrors)) 
            {
                DB::commit();
                $success['userMovie'] = json_encode(array('$movidId' =>  request('movieId'), 'title' => request('title'), 'lengthTotal' => request('lengthTotal'), 'year' => request('year'), 'rating' => request('rating')));
                return response()->json(['success' => $success], $this->successStatus);
            }
            else
            {
                //put DB::rollback(); everywhere an exception is found, in case we dont get to this spot
                return response()->json(['errors'=>$foundErrors], 500);                      
            }                
        }
        else
        {
            return response()->json(['error'=>'Unauthorised'], 401);
        }
    }

    /**
    * createUserMovie api
    *
    * @return \Illuminate\Http\Response
    */
    public function deleteUserMovie($id)
    {        
        //since we are using the auth::user id and the passed in movie id on the user_movie table, 
        //we should be protecting other user's data (if somone passes in a movie id that is not part of the user_movie PK pair)
        Log::info('in MoviesAPIController deleteUserMovie... id='.$id);
        
                    
        if(Auth::check())
        {
            Log::info('in MoviesAPIController deleteUserMovie - Auth passed');                
           
            if (is_null($id) || $id < 1) 
            {
                Log::info('error in MoviesAPIController deleteUserMovie - missing movieId');
                return response()->json(['error'=>['missing required field'=>'movieId']], 500);            
            }
            
            DB::beginTransaction();
        
            $timestamp = date("Y-m-d H:i:s");
            $user = Auth::user();
            $foundErrors = array();

            $rowsAffected = 0;

            try 
            {                   
                Log::info('now delete user_movies in MoviesAPIController deleteUserMovie - ');   

                $rowsAffected = DB::table('user_movies')                
                                ->where('userId', '=', $user->id)                
                                ->where('movieId','=', $id)
                                ->delete();

                Log::info('after delete user_movies... rows affected = '.$rowsAffected);                                
            } 
            catch(ValidationException $e)
            {  
                DB::rollback();
                $foundErrors[] =  $e->getErrors();  //get error to send back with response
                Log::info('ValidationException in MoviesAPIController deleteUserMovie user_movies update - '. $e->getErrors());
            }
            catch(\Exception $e)
            {
                Log::info('Exception in MoviesAPIController deleteUserMovie user_movies update - e');
                DB::rollback();
                throw $e;
            }

            Log::info('done with user_movies in MoviesAPIController deleteUserMovie ');

            if($rowsAffected == 0 )
            {
                $foundErrors[] = "Failed to delete user_movies!";               
            } 

            if (empty($foundErrors)) 
            {
                $rowsAffected = 0;
                Log::info('now delete movies in MoviesAPIController deleteUserMovie - ');

                try 
                {
                    $rowsAffected = DB::table('movies')
                                        ->where('id', $id)
                                        ->delete();
                    
                    Log::info('after delete movies... rows affected = '.$rowsAffected);
                } 
                catch(ValidationException $e)
                {                            
                    $rowsAffected = DB::rollback();
                    $foundErrors[] =  $e->getErrors();  //get error to send back with response
                    Log::info('ValidationException in MoviesAPIController deleteUserMovie movies delete - '. $e->errors());
                }
                catch(\Exception $e)
                {
                    Log::info('Exception in MoviesAPIController deleteUserMovie movies delete - e');
                    DB::rollback();
                    throw $e;
                }
            }

            Log::info('done with movies in MoviesAPIController deleteUserMovie - ');

            if($rowsAffected == 0 )
            {
                $foundErrors[] = "Failed to delete movies!";
                DB::rollback();
            } 

            Log::info($foundErrors);
            if (empty($foundErrors)) 
            {
                DB::commit();
                Log::info('commit done');
                $success['userMovie'] = json_encode(array('$movidId' =>  request('movieId'), 'title' => request('title'), 'lengthTotal' => request('lengthTotal'), 'year' => request('year'), 'rating' => request('rating')));
                return response()->json(['success' => 'Success'], $this->successStatus);
            }
            else
            {
                //put DB::rollback(); everywhere an exception is found, in case we dont get to this spot
                return response()->json(['errors'=>$foundErrors], 500);                      
            }                
        }
        else
        {
            return response()->json(['error'=>'Unauthorised'], 401);
        }
    }



    /**
    * login getMovieFormats
    *
    * @return \Illuminate\Http\Response
    */
    public function getMovieFormats(Request $request)
    {
        Log::info('in MoviesAPIController getMovieFormats... ');

        
        if(Auth::check())
        {
            Log::info('in MoviesAPIController getMovieFormats - Auth passed');

            $user = Auth::user();
            
            $success['formats'] = DB::table('formats')
            
            ->select('formats.id', 'formats.name')
            ->where('formats.active', '>', 0)
            ->get();

            
            return response()->json(['success' => $success], $this->successStatus);                
        }
        else
        {
            return response()->json(['error'=>'Unauthorised'], 401);
        }
    }


    //TODO: create calls to crud formats ... delete will have ramifications that need to be handled 
}
