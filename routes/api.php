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

/*
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
*/

Route::group(['prefix' => 'v1'], function () {
    Route::post('signup','AuthController@signup');
    Route::post('login','AuthController@login');

    Route::group(['middleware' => ['auth:api']], function () {
        Route::get('logout', 'AuthController@logout');
        Route::get('user', 'AuthController@user');

        //Buyer Route
        Route::get('/buyer','BuyerController@index');
        Route::get('/buyer/{id}','BuyerController@show');
        Route::post('/buyer','BuyerController@store');
        Route::put('/buyer/{data}','BuyerController@update');
        Route::delete('/buyer/{id}','BuyerController@delete');

        //Agent Route
        Route::get('/agent','AgentController@index');
        Route::get('/agent/{id}','AgentController@show');
        Route::post('/agent','AgentController@store');
        Route::put('/agent/{data}','AgentController@update');
        Route::delete('/agent/{id}','AgentController@delete');

        //Supplier Route
        Route::get('/supplier','SupplierController@index');
        Route::get('/supplier/{id}','SupplierController@show');
        Route::post('/supplier','SupplierController@store');
        Route::put('/supplier/{data}','SupplierController@update');
        Route::delete('/supplier/{id}','SupplierController@delete');    

    });

});


