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
Route::post('/dji-login', 'UserController@djiLogin');
Route::post('/dji-register', 'UserController@djiRegister');
Route::post('/dji-inquiry', 'UserController@djiInquiry');
Route::post('/dji-payment', 'UserController@djiPayment');

// banks
Route::get('/banks/get-recipient', 'OrderController@getRecipientBanks');
Route::get('/banks/get-sender', 'OrderController@getSenderBanks');

// top up
Route::get('/topup/get-nominals', 'OrderController@getTopUpNominals');
Route::get('/topup/get-orders', 'OrderController@getTopUpOrders');
Route::post('/topup/create-order', 'OrderController@createTopUpOrder');
Route::post('/topup/confirm-order', 'OrderController@confirmTopUpOrder');
Route::post('/topup/verify-order', 'OrderController@verifyTopUpOrder');