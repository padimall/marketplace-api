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
    Route::post('login-dev','AuthController@login_dev');

    Route::group(['middleware' => ['auth:api','scope:access-all-system']], function () {
        Route::get('logout', 'AuthController@logout');
        Route::get('user', 'AuthController@user');

        //Buyer Route
        Route::post('/buyer','BuyerController@index');

        //Agent Route
        Route::post('/agent','AgentController@index');

        //Supplier Route
        Route::post('/supplier','SupplierController@index');

        //Agents Affiliate Supplier Route
        Route::post('/agents-affiliate-supplier','AgentsAffiliateSupplierController@index');

        Route::post('/product-category','ProductsCategoryController@index');
        Route::post('/product','ProductController@index');
        Route::post('/product-image','ProductsImageController@index');
        Route::post('/cart','CartController@index');

        Route::post('/invoice','InvoiceController@index');
        Route::post('/invoice-product','InvoicesProductController@index');


    });

});


