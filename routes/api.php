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
Route::post('total','ProductController@total');
Route::post('register-logs','AuthController@getLog');
Route::post('send-notif','InvoiceController@sendNotif');
Route::post('callback','InvoiceController@callback');
Route::post('callback-ewallet','InvoiceController@callback_ewallet');
Route::get('email/verify/', 'VerificationController@verify')->name('verification.verify'); // Make sure to keep this as your route name

Route::get('email/resend', 'VerificationController@resend')->name('verification.resend');

Route::group(['prefix' => 'v1'], function () {
    Route::post('password/forgot', 'ForgotPasswordController@forgot');
    Route::post('password/reset', 'ForgotPasswordController@changePassword')->name('password.reset');


    Route::post('signup','AuthController@signup');
    Route::post('signup-admin','AuthController@signup_admin');

    Route::post('login','AuthController@login');
    Route::post('login-dev','AuthController@login_dev');
    Route::post('login-admin','AuthController@login_dev');

    Route::group(['prefix' => 'product'], function () {
        Route::post('/all','ProductController@showAll');
        Route::post('/detail','ProductController@show');
        Route::post('/limit','ProductController@showLimit');
        Route::post('/search','ProductController@product_search');
        Route::post('/category','ProductController@product_category');
        Route::post('/shop','ProductController@product_shop');
        Route::post('/main-category','ProductController@product_main_category');
        Route::post('/agent-id','ProductController@product_agent_id');
        Route::post('/supplier-id','ProductController@product_supplier_id');
        Route::post('/rating','ProductController@ratings');
    });

    Route::group(['prefix' => 'main-category'], function () {
        Route::post('/detail-id','AgentController@detail_id');
    });

    Route::group(['prefix' => 'main-category'], function () {
        Route::post('/all','MainCategoryController@showAll');
        Route::post('/detail','MainCategoryController@show');
        Route::post('/limit','MainCategoryController@showLimit');
        Route::post('/sub','MainCategoryController@sub');
    });

    Route::group(['prefix' => 'product-category'], function () {
        Route::post('/all','ProductsCategoryController@showAll');
        Route::post('/detail','ProductsCategoryController@show');
        Route::post('/limit','ProductsCategoryController@showLimit');
    });

    Route::group(['prefix' => 'banner'], function () {
        Route::post('/all','BannerController@showAll');
        Route::post('/detail','BannerController@show');
        Route::post('/type','BannerController@type');
    });


    Route::group(['middleware' => ['auth:api','scopes:system-token,user-token']], function () {

        Route::post('/user',function(){
            return response()->json(request()->user());
        });

        Route::group(['prefix' => 'user'], function () {
            Route::post('/update','AuthController@update');
            Route::post('/change-password','AuthController@password');
        });

        Route::group(['prefix' => 'agent'], function () {
            Route::post('/detail','AgentController@show');
            Route::post('/update','AgentController@update');
            Route::post('/store','AgentController@store');
			Route::post('/delete-image','AgentController@delete_image');
        });

        Route::group(['prefix' => 'supplier'], function () {
            Route::post('/detail','SupplierController@show');
            Route::post('/store','SupplierController@store');
            Route::post('/update','SupplierController@update');
            Route::post('/my-agent','SupplierController@myagent');
            Route::post('/delete-image','SupplierController@delete_image');
        });

        Route::group(['prefix' => 'product'], function () {
            Route::post('/store','ProductController@store');
            Route::post('/update','ProductController@update');
            Route::post('/update-status','ProductController@update_status');
            Route::post('/agent','ProductController@product_agent');
            Route::post('/supplier','ProductController@product_supplier');
            Route::post('/delete','ProductController@delete');
            Route::post('/my-supplier','ProductController@product_my_supplier');
        });


        Route::group(['prefix' => 'product-image'], function () {
            Route::post('/all','ProductsImageController@showAll');
            Route::post('/detail','ProductsImageController@show');
            Route::post('/limit','ProductsImageController@showLimit');
            Route::post('/store','ProductsImageController@store');
            Route::post('/update','ProductsImageController@update');
            Route::post('/delete','ProductsImageController@delete');
        });

        Route::group(['prefix' => 'cart'], function () {
            Route::post('/detail','CartController@show');
            Route::post('/store','CartController@store');
            Route::post('/update','CartController@update');
            Route::post('/user','CartController@list');
            Route::post('/delete','CartController@delete');
        });

        Route::group(['prefix' => 'checkout'], function () {
            Route::post('/user','CheckoutController@checkout');
        });

        Route::group(['prefix' => 'invoice'], function () {
            Route::post('/detail','InvoiceController@show');
            Route::post('/store','InvoiceController@store');
            Route::post('/update','InvoiceController@update');
            Route::post('/track','InvoiceController@track');
            Route::post('/list','InvoiceController@list');
            Route::post('/add-resi','InvoiceController@add_resi');
            Route::post('/pay','InvoiceController@pay');
            Route::post('/seller','InvoiceController@invoice_seller');
            Route::post('/receive','InvoiceController@receive_product');
            Route::post('/group-detail','InvoiceController@invoice_group_detail');
            Route::post('/testing','InvoiceController@testing');

        });

        Route::group(['prefix' => 'invoice-product'], function () {
            Route::post('/detail','InvoicesProductController@show');
            Route::post('/store','InvoicesProductController@store');
            Route::post('/update','InvoicesProductController@update');
        });

        Route::group(['prefix' => 'invoice-product-rating'], function () {
            Route::post('/store','InvoicesProductRatingController@store');
            Route::post('/update','InvoicesProductRatingController@update');
        });

        Route::group(['prefix' => 'invoice-product-rating-image'], function () {
            Route::post('/store','InvoiceProductRatingImageController@store');
            Route::post('/delete','InvoiceProductRatingImageController@delete');
        });

        Route::group(['prefix' => 'payment'], function () {
            Route::post('/all','PaymentController@showAll');
        });


        Route::group(['prefix' => 'admin-price'], function () {
            Route::post('/all','AdminPriceController@showAll');
            Route::post('/detail','AdminPriceController@show');
        });
    });

    Route::group(['middleware' => ['auth:api','scope:system-token']], function () {

        Route::group(['prefix' => 'user'], function () {
            Route::post('/all','AuthController@showAll');
            Route::post('/detail','AuthController@show');
        });

        Route::group(['prefix' => 'agent'], function () {
            Route::post('/all','AgentController@showAll');
            Route::post('/limit','AgentController@showLimit');
            Route::post('/update-status','AgentController@update_status');
        });

        Route::group(['prefix' => 'banner'], function () {
            Route::post('/store','BannerController@store');
            Route::post('/update','BannerController@update');
            Route::post('/delete','BannerController@delete');
        });

        Route::group(['prefix' => 'payment'], function () {
            Route::post('/store','PaymentController@store');
            Route::post('/update','PaymentController@update');
        });

        Route::group(['prefix' => 'supplier'], function () {
            Route::post('/all','SupplierController@showAll');
            Route::post('/detail-id','SupplierController@detail_id');
            Route::post('/limit','SupplierController@showLimit');
            Route::post('/update-status','SupplierController@update_status');
        });


        Route::group(['prefix' => 'agents-affiliate-supplier'], function () {
            Route::post('/all','AgentsAffiliateSupplierController@showAll');
            Route::post('/detail','AgentsAffiliateSupplierController@show');
            Route::post('/store','AgentsAffiliateSupplierController@store');
            Route::post('/update','AgentsAffiliateSupplierController@update');
            Route::post('/limit','AgentsAffiliateSupplierController@showLimit');
        });

        Route::group(['prefix' => 'product'], function () {
            Route::post('/admin-delete','ProductController@delete_admin');
        });

        Route::group(['prefix' => 'product-category'], function () {
            Route::post('/store','ProductsCategoryController@store');
            Route::post('/update','ProductsCategoryController@update');
        });

        Route::group(['prefix' => 'main-category'], function () {
            Route::post('/store','MainCategoryController@store');
            Route::post('/update','MainCategoryController@update');
        });

        Route::group(['prefix' => 'cart'], function () {
            Route::post('/all','CartController@showAll');
            Route::post('/limit','CartController@showLimit');
        });


        Route::group(['prefix' => 'invoice'], function () {
            Route::post('/all','InvoiceController@showAll');
            Route::post('/limit','InvoiceController@showLimit');
            Route::post('/transaction-info','InvoiceController@transaction_info');
        });


        Route::group(['prefix' => 'invoice-product'], function () {
            Route::post('/all','InvoicesProductController@showAll');
            Route::post('/limit','InvoicesProductController@showLimit');
        });

        Route::group(['prefix' => 'invoice-product-rating'], function () {
            Route::post('/censor','InvoicesProductRatingController@censored');
        });

        Route::group(['prefix' => 'admin-price'], function () {
            Route::post('/store','AdminPriceController@store');
            Route::post('/update','AdminPriceController@update');
        });

    });

});


