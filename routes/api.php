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

// prics trake
Route::post('/changprics', 'ApiController@changprics_api');
Route::post('/postcookies', 'ApiController@postcookies');
Route::get('/getlastupdate', 'ApiController@getlastupdate');
Route::post('/poststatus', 'ApiController@poststatus');
Route::get('/getstatus', 'ApiController@getstatus');


//close orders
Route::get('/get_processing_orders', 'git_data@processing_orders');


//track ads
Route::post('/post_track_amount_and_price', 'track_controller@post_track_amount_and_price');
Route::post('/post_track_status', 'track_controller@post_track_status');
Route::get('/git_track_data', 'track_controller@git_track_data');
Route::get('/git_track_data2', 'track_controller@track_orders');

//progress order
Route::get('/git_progress_order', 'progress_orders@git_progress_order');
Route::get('/git_order_otp', 'progress_orders@git_order_otp');
Route::post('/update_progress_order', 'progress_orders@update_progress_order');
Route::get("/new_sms_massage/name/{name}/number/{number}/message/{message}", 'progress_orders@new_sms_massage');





Route::group(['middleware' => ['auth:sanctum']], function () {
    // middleware routes here
    Route::post('/authCheck', 'ApiController@authCheck');
    Route::post('/logout', 'ApiController@logout');
});
