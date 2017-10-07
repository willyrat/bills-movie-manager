<?php

namespace App\Providers;

//use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        Schema::defaultStringLength(191);   //put in for passport - bpratt 20171007 migration caused mysql error key too long 
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
        //Passport::ignoreMigrations;
    }
}
