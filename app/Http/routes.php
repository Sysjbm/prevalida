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

Route::get('/validador', function () {
    return view('validador');
});

# Import
Route::get('uploadfile','ImportController@uploadfile');
Route::post('uploadfile','ImportController@uploadFilePost');
Route::post('pdf', 'ImportController@descargarLog');