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

// user
Route::get('/user', 'UserController@show');
Route::post('/login', 'UserController@login');
Route::post('/register', 'UserController@register');
Route::post('/logout', 'UserController@logout');
Route::post('/send-verification-code', 'UserController@sendVerificationCode');
Route::post('/verify', 'UserController@verify');
Route::post('/reset-password', 'UserController@resetPassword');
Route::post('/update-profile', 'UserController@updateProfile');
Route::post('/update-fcm-token', 'UserController@updateFCMToken');
Route::get('/get-credit-card-token', 'UserController@getCreditCardToken');
Route::get('/get-balance-details', 'UserController@getBalanceDetails');

// banks
Route::get('/banks/get-recipient', 'ProductController@getRecipientBanks');
Route::get('/banks/get-sender', 'ProductController@getSenderBanks');

// products
Route::get('/products/get-pdam', 'ProductController@getPDAMProducts');
Route::get('/products/get-pln', 'ProductController@getPrepaidPLNProducts');
Route::get('/products/get-tv', 'ProductController@getTVProducts');
Route::get('/products/get-finance', 'ProductController@getFinanceProducts');
Route::post('/products/get-pulsa-postpaid', 'ProductController@getPostpaidPulsaProduct');

// order
Route::post('/use-promo-code', 'OrderController@usePromoCode');
Route::post('/create-order', 'OrderController@createOrder');
Route::post('/save-promo-code', 'OrderController@savePromoCode');
Route::post('/save-payment-method', 'OrderController@savePaymentMethod');
Route::post('/confirm-order', 'OrderController@confirmOrder');


