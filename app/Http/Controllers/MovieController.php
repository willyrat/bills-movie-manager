<?php

namespace App\Http\Controllers;

//use App\API\ApiHelper;
use App\Http\Controllers\Controller;
use Log;
use Illuminate\Support\Facades\Input;

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
        return view('movies');
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
}
