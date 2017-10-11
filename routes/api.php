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

Route::middleware('auth:api')->get('/user', function (Request $request) 
{
    //Log::info('in middleware auth:api');
    return $request->user();
});


// Route::get('/user', function(Request $request) {
//     return $request->user();
// })->middleware('auth:api');

// Route::get('/user', function (Request $request)
// {
//     return $request->user();  
// });



//put in for passport - bpratt 20171007
//Route::post('login', 'API\PassportController@login');
//Route::post('register', 'API\PassportController@register');



Route::group(['middleware' => 'auth:api'], function()
{
    Route::post('get-details', 'API\PassportController@getDetails');
    
    //Route::get('movies', 'MovieController@index')->name('movies');
});


// Route::resource(
//     'movies', 'MovieController',
//     [ 'except' => ['create', 'edit'] ]
// );


//Route::post('userMovies', 'API\MoviesController@userMovies');




