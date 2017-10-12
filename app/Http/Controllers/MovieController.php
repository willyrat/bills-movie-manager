<?php

namespace App\Http\Controllers;

//use App\API\ApiHelper;
use App\Http\Controllers\Controller;
use Log;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\UserMovies;
use Illuminate\Support\Facades\DB;

class MovieController extends Controller
{
   // use ApiHelper;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
     public function __construct()
     {
        Log::info('session token = '.\Session::token());
        
        Log::info('!!!!!!!!!!!!!!!!!!!!!!!!!IN movies controller.constructor');
        //  $this->middleware('auth:api');
     }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        Log::info('!!!!!!!!!!!!!!!!!!!!!!!!!IN movies controller.index');      
        
        //$users = User::paginate(5);
        
        //return view('movies',compact('users'));
        //return view('movies');
        
        $user = Auth::user();

        $userMovies = DB::table('user_movies')
        ->join('users', 'users.id', '=', 'user_movies.userId')
        ->join('movies', 'movies.id', '=', 'user_movies.movieId')
        ->select('users.id', 'users.firstName', 'users.lastName','movies.id', 'movies.title', 'movies.length', 'movies.releaseYear', 'user_movies.rating')
        ->where('users.id', '=', $user->id)
        ->get();

        
log::info('user id = '.$user->id);
log::info('sql = '. DB::table('user_movies')
->join('users', 'users.id', '=', 'user_movies.userId')
->join('movies', 'movies.id', '=', 'user_movies.movieId')
->select('users.id', 'users.firstName', 'users.lastName','movies.id', 'movies.title', 'movies.length', 'movies.releaseYear', 'user_movies.rating')
->where('users.id', '=', $user->id)
->toSql());


        return view('movies',compact('userMovies'));
    }



    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        // public function create($userId, $name, $redirect, $personalAccess = false, $password = false)
        // {
        //     $client = (new Client)->forceFill([
        //         'user_id' => $userId,
        //         'name' => $name,
        //         'secret' => str_random(40),
        //         'redirect' => $redirect,
        //         'personal_access_client' => $personalAccess,
        //         'password_client' => $password,
        //         'revoked' => false,
        //     ]);
    
        //     $client->save();
    
        //     return $client;
        // }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function myPagination()
    
        {
    
            
    
        }
}
