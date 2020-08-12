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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});



//buyer

Route::group([
    'prefix' => 'auth'
], function () {
    Route::post('login', 'AuthController@login');
    Route::post('signup', 'AuthController@signup');

    Route::group([
      'middleware' => 'auth:api'
    ], function() {
        Route::get('logout', 'AuthController@logout');
        Route::get('user', 'AuthController@user');

        //buyer
        Route::get('/buyer','ControllerBuyer@index');
        Route::post('/buyer','ControllerBuyer@store');

        //product
        Route::get('/product','ControllerProduct@index');
        Route::get('/product/{id}','ControllerProduct@show');
        Route::post('/product','ControllerProduct@store');
        Route::put('/product/{product}','ControllerProduct@update');
        Route::delete('/product/{id}','ControllerProduct@delete');
    });
});
