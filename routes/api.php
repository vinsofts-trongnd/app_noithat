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


Route::get('register', 'UserPhoneNumberController@index');
Route::get('register/{userphone}', 'UserPhoneNumberController@show');
Route::post('register', 'UserPhoneNumberController@store');
Route::put('register/{userphone}', 'UserPhoneNumberController@update');
Route::delete('register/{userphone}', 'UserPhoneNumberController@delete');
