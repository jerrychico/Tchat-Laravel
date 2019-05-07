<?php

use Illuminate\Http\Request;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => ['auth:api']], function () {
    Route::get('/conversations','Api\conversationController@index');
    Route::get('/conversations/{user}','Api\conversationController@show');
    Route::post('/conversations/{user}','Api\conversationController@store');
    Route::post('/messages/{message}','Api\MessagesController@read')->middleware('can:read,message');
});
