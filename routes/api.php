<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::post('/changprics', 'ApiController@changprics');
Route::post('/postcookies', 'ApiController@postcookies');

Route::group(['middleware' => ['auth:sanctum']], function () {
    // middleware routes here
    Route::post('/authCheck', 'ApiController@authCheck');
    Route::post('/logout', 'ApiController@logout');
});