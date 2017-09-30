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

// dji
Route::post('/dji/sign-on', 'DjiController@signOn');
Route::post('/dji/inquiry', 'DjiController@inquiry');
Route::post('/dji/payment', 'DjiController@payment');

// banks
Route::get('/banks/get-recipient', 'ProductController@getRecipientBanks');
Route::get('/banks/get-sender', 'ProductController@getSenderBanks');

// products
Route::get('/products/get-saldo', 'ProductController@getSaldoProducts');
Route::get('/products/get-pdam', 'ProductController@getPDAMProducts');
Route::get('/products/get-pln', 'ProductController@getPrepaidPLNProducts');
Route::get('/products/get-tv', 'ProductController@getTVProducts');
Route::get('/products/get-finance', 'ProductController@getFinanceProducts');
Route::post('/products/get-pulsa-postpaid', 'ProductController@getPostpaidPulsaProduct');

// order - product
Route::post('/products/{product}/check-invoice', 'OrderController@checkInvoice');
Route::post('/products/{product}/use-promo-code', 'OrderController@usePromoCode');
Route::post('/products/{product}/create-order', 'OrderController@createOrder');
Route::post('/products/{product}/confirm-order', 'OrderController@confirmOrder');


