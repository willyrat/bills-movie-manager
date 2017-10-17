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
            $tableInfo['length'] = 'movies';
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
            ->select('movies.id', 'users.firstName', 'users.lastName','movies.id', 'movies.title', DB::raw('FLOOR(movies.length/60) as lengthHour') , DB::raw('MOD(movies.length,60) as lengthMinute') ,'movies.releaseYear', 'user_movies.rating', 'user_movies.formatId')
            ->where('users.id', '=', $user->id)
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
                'title' => 'required',
                'lengthHour' => 'required',
                'lengthMinute' => 'required',
                'year' => 'required',
                'rating' => 'required',                    
                'formatId' => 'required',                    
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
                    'length' => request('lengthHour') * 60 + request('lengthMinute'), 
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
                $success['userMovie'] = json_encode(array('$movidId' => $movidId, 'title' => request('title'), 'lengthc' => request('length'), 'year' => request('year'), 'rating' => request('rating')));
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
    public function updateUserMovie(Request $request)
    {
        //since we are using the auth::user id and the passed in movie id on the user_movie table, 
        //we should be protecting other user's data (if somone passes in a movie id that is not part of the user_movie PK pair)
        Log::info('in MoviesAPIController updateUserMovie... ');
        
                    
        if(Auth::check())
        {
            Log::info('in MoviesAPIController updateUserMovie - Auth passed');                
            
                $validator = Validator::make($request->all(), 
                [
                    'movieId' => 'required',                                        
                    'formatId' => 'required',                                        
                ]);                

            if ($validator->fails()) 
            {
                Log::info('error in MoviesAPIController updateUserMovie - '. $validator->errors());
                return response()->json(['error'=>$validator->errors()], 500);            
            }

            DB::beginTransaction();
                            
                $timestamp = date("Y-m-d H:i:s");
                $user = Auth::user();
                $foundErrors = array();

                try 
                {   
                    Log::info('now update movies in MoviesAPIController updateUserMovie - ');   
                    
                    DB::table('user_movies')                
                        ->where('userId', '=', $user->id)                
                        ->where('movieId','=', request('movieId'))
                        ->update([  'formatId' => request('formatId'),                //TODO: need to fix this to work with table
                                    'rating' => request('rating'),                                    
                                    'modifiedDate' => $timestamp,
                                    'modifiedBy' => $user->id
                                ]);

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

                if (empty($foundErrors)) 
                {
                    Log::info('now update movies in MoviesAPIController updateUserMovie - ');
                    try 
                    {
                        DB::table('movies')
                        ->where('id', request('movieId'))
                        ->update([  'title' => request('title'),
                                    'length' => request('lengthHour') * 60 + request('lengthMinute'),
                                    'releaseYear' => request('year'),
                                    'modifiedDate' => $timestamp,
                                    'modifiedBy' => $user->id
                                ]);
                        
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
            if (empty($foundErrors)) 
            {
                DB::commit();
                $success['userMovie'] = json_encode(array('$movidId' =>  request('movieId'), 'title' => request('title'), 'length' => request('length'), 'year' => request('year'), 'rating' => request('rating')));
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

            try 
            {   
                Log::info('now delete user_movies in MoviesAPIController deleteUserMovie - ');   

                DB::table('user_movies')                
                ->where('userId', '=', $user->id)                
                ->where('movieId','=', $id)
                ->delete();

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

            if (empty($foundErrors)) 
            {
                Log::info('now delete movies in MoviesAPIController deleteUserMovie - ');
                try 
                {
                    DB::table('movies')
                    ->where('id', $id)
                    ->delete();
                    
                } 
                catch(ValidationException $e)
                {                            
                    DB::rollback();
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
            Log::info($foundErrors);
            if (empty($foundErrors)) 
            {
                DB::commit();
                Log::info('commit done');
                //$success['userMovie'] = json_encode(array('$movidId' =>  request('movieId'), 'title' => request('title'), 'length' => request('length'), 'year' => request('year'), 'rating' => request('rating')));
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
            ->get();;

            
            return response()->json(['success' => $success], $this->successStatus);                
        }
        else
        {
            return response()->json(['error'=>'Unauthorised'], 401);
        }
    }


    //TODO: create calls to crud formats ... delete will have ramifications that need to be handled 
}
