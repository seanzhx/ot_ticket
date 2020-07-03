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

Route::get('/', function () {
    return view('welcome');
});

Route::get('request/token', 'RequestController@token')->name('request.token');
Route::get('request/attendance', 'RequestController@attendance')->name('request.attendance');
Route::get('request/ticket', 'RequestController@ticket')->name('request.ticket');
