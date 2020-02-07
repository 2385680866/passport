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
Route::get("test/md5SignGet","TestController@md5SignGet");//测试签名GET
Route::post("test/md5SignPost","TestController@md5SignPost");//测试签名POST

