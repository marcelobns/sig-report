<?php
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Route::get('/', 'PageController@home');
Route::get('/censup', 'PageController@censup');
Route::get('/gda', 'PageController@gda');

Route::get('/discentes', 'DiscenteController@index');
Route::get('/discente/{id}', 'DiscenteController@view');

Auth::routes();
