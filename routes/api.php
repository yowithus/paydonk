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

// authentication
Route::post('/login', 'UserController@login');
Route::post('/register', 'UserController@register');
Route::post('/logout', 'UserController@logout');
Route::post('/send-verification-code', 'UserController@sendVerificationCode');
Route::post('/verify', 'UserController@verify');
Route::post('/reset-password', 'UserController@resetPassword');

Route::get('/user', 'UserController@show');

// dji
Route::post('/dji/sign-on', 'DjiController@signOn');
Route::post('/dji/inquiry', 'DjiController@inquiry');
Route::post('/dji/payment', 'DjiController@payment');

// banks
Route::get('/banks/get-recipient', 'OrderController@getRecipientBanks');
Route::get('/banks/get-sender', 'OrderController@getSenderBanks');

// top up
Route::get('/topup/get-nominals', 'OrderController@getTopUpNominals');
Route::get('/topup/get-orders', 'OrderController@getTopUpOrders');
Route::post('/topup/create-order', 'OrderController@createTopUpOrder');
Route::post('/topup/confirm-order', 'OrderController@confirmTopUpOrder');
Route::post('/topup/verify-order', 'OrderController@verifyTopUpOrder');

// products
Route::get('/products/{product_code}/get-nominals', 'OrderController@getNominals');
Route::post('/products/{product_code}/get-invoice', 'OrderController@getInvoice');
Route::post('/products/{product_code}/create-order', 'OrderController@createOrder');
Route::post('/products/{product_code}/confirm-order', 'OrderController@confirmOrder');
Route::post('/products/{product_code}/verify-order', 'OrderController@verifyOrder');


