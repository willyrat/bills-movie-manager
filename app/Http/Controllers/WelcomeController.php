<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    //

    /**
     * Create a new controller instance.
     *
     * @return void
     */
     public function __construct()
     {
         
     }

    public function welcome()
    {
        return view('welcome');
    }
}
