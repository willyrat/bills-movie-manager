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
    * login api
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
        
        $success['userMovies'] = DB::table('user_movies')
        ->join('users', 'users.id', '=', 'user_movies.userId')
        ->join('movies', 'movies.id', '=', 'user_movies.movieId')
        ->select('movies.id', 'users.firstName', 'users.lastName','movies.id', 'movies.title', 'movies.length', 'movies.releaseYear', 'user_movies.rating')
        ->where('users.id', '=', $user->id)
        ->get();;

        
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
                'length' => 'required',
                'year' => 'required',
                'rating' => 'required',                    
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
                    'length' => request('length'), 
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
            } catch(ValidationException $e)
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
                        'formatId' => 1, 
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
                } catch(ValidationException $e)
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
                
                    //only update what is being passed in
                    $updateParams = array();    
                    //$rating = request('rating');             
                   
                    strlen(request('formatId')) > 0 ? $updateParams['formatId'] = request('formatId') : $updateParams = ['formatId'=>1];
                    strlen(request('rating')) > 0 ? $updateParams['rating'] = request('rating') : 1;
                    
                    $updateParams['modifiedDate'] = $timestamp;
                    $updateParams['modifiedBy'] = $user->id;
                    Log::info($updateParams );
                    DB::table('user_movies')                
                        ->where('userId', '=', $user->id)                
                        ->where('movieId','=', request('movieId'))
                        ->update($updateParams);

                } catch(ValidationException $e)
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

                    //only update what is being passed in
                    unset($updateParams);                   
                    strlen(request('title')) >0 ? $updateParams['title'] = request('title') : 1;
                    strlen(request('length')) >0 ? $updateParams['length'] = request('length') : 1;
                    strlen(request('releaseYear')) >0 ? $updateParams['year'] = request('year') : 1;
                    
                    $updateParams['modifiedDate'] = $timestamp;
                    $updateParams['modifiedBy'] = $user->id;
                    Log::info($updateParams );
                        DB::table('movies')
                        ->where('id', request('movieId'))
                        ->update($updateParams);
                        
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
    public function deleteUserMovie(Request $request)
    {
        //since we are using the auth::user id and the passed in movie id on the user_movie table, 
        //we should be protecting other user's data (if somone passes in a movie id that is not part of the user_movie PK pair)


      //  ->where('votes', '>', 100)->delete();

    }
}
