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
                ->select('users.id', 'users.firstName', 'users.lastName','movies.id', 'movies.title', 'movies.length', 'movies.releaseYear', 'user_movies.rating')
                ->where('users.id', '=', $user->id)
                ->get();;

                
                return response()->json(['success' => $success], $this->successStatus);                
            }
            else
            {
                return response()->json(['error'=>'Unauthorised'], 401);
            }
        }
}
