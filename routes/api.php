<?php

use Illuminate\Http\Request;
//use Illuminate\Support\Facades\Route;

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

/*Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});*/

Route::post('add_user', 'API\UserController@addUser');
Route::post('register', 'API\UserController@register');
Route::post('verify_pin', 'API\UserController@verifyPin');
Route::post('login', 'API\UserController@login');
Route::group(['middleware' => ['jwt.verify']], function() {
	Route::post('update_profile', 'API\UserController@editProfile');
});	
