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

//Route::get('/test', 'TestController@index');
Route::get('/profile', 'HomeController@profile')->name('profile');

Route::post('/file/upload', 'HomeController@upload')->name('file.upload');


Route::get('/home', 'HomeController@index');
Route::get('/import', 'ImportController@index');

Route::get('/', function () {
    return view('welcome');
});
