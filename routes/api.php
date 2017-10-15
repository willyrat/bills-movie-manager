<?php

use Illuminate\Http\Request;
//use Log;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/



// Route::get('/user', function(Request $request) {
//     return $request->user();
// })->middleware('auth:api');

// Route::get('/user', function (Request $request)
// {
//     return $request->user();  
// });



//put in for passport - bpratt 20171007
Route::post('login', 'API\PassportController@login');
Route::post('register', 'API\PassportController@register');



Route::group(['middleware' => 'auth:api'], function()
{
    Route::post('get-details', 'API\PassportController@getDetails');

    Route::get('get-user-movies', 'API\MoviesAPIController@getUserMovies');
    Route::get('get-formats', 'API\MoviesAPIController@getMovieFormats');
    Route::post('create-movie', 'API\MoviesAPIController@createUserMovie');    
    Route::put('update-movie/{id}', 'API\MoviesAPIController@updateUserMovie');
    Route::delete('delete-movie/{id}', 'API\MoviesAPIController@deleteUserMovie');

    Route::get('check-auth', 'API\PassportController@checkAuth');
    
    //Route::get('movies', 'MovieController@index')->name('movies');
});


// Route::resource(
//     'movies', 'MovieController',
//     [ 'except' => ['create', 'edit'] ]
// );


//Route::post('userMovies', 'API\MoviesController@userMovies');




