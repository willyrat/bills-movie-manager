<?php


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


// Route::get('/', function () 
// {

//     // try 
//     // {
//     //     DB::connection()->getPdo();
//     //     if(DB::connection()->getDatabaseName())
//     //     {
//     //         echo "Yes! Successfully connected to the DB: " . DB::connection()->getDatabaseName();
//     //     }
//     // } 
//     // catch (\Exception $e) 
//     // {
//     //     die("Could not connect to the database.  Please check your configuration.");
//     // }

//     return view('welcome');
// });

Auth::routes();

// Route::get('/register', function () 
// {    
    
//     return view('auth/register');
// });



Route::group(['middleware' => 'web'], function()
{
   

    //Route::post('get-details', 'API\PassportController@getDetails');
    
   // Route::get('movies', 'MovieController@index')->name('movies');
});

Route::group(['middleware' => 'auth:api'], function()
{
    //Route::post('get-details', 'API\PassportController@getDetails');
    
   // Route::get('movies', 'MovieController@index')->name('movies');
});



Route::get('/movies', 'MovieController@index')->name('movies');

Route::get('mypagination', 'MovieController@myPagination');



Route::get('/', 'WelcomeController@welcome')->name('welcome');
Route::get('/home', 'HomeController@index')->name('home');



