<?php

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

Route::group(['namespace' => 'Api'], function () {
    Route::post('/token', 'AuthController@postGenerateToken');

    Route::group(['middleware' => ['auth:sanctum', 'power:edit_data']], function () {
        Route::get('/character', 'InfoController@getCharacter');
    });
});
