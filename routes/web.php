<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('welcome');
});

Route::get('auth', 'AuthController@checkUser');
Route::post('login', 'AuthController@login');
Route::get('logout', 'AuthController@logout');
Route::get('errors', 'ErrorController@index');
Route::post('errors', 'ErrorController@store');

Route::resource('sermons', 'SermonController');