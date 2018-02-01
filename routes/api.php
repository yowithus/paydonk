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
Route::post('/login', 'UserController@login');
Route::post('/register', 'UserController@register');
Route::post('/logout', 'UserController@logout');
Route::post('/send-verification-code', 'UserController@sendVerificationCode');
Route::post('/verify', 'UserController@verify');
Route::post('/reset-password', 'UserController@resetPassword');
Route::post('/update-profile', 'UserController@updateProfile');
Route::post('/update-profile-picture', 'UserController@updateProfilePicture');
Route::post('/update-fcm-token', 'UserController@updateFCMToken');
Route::post('/verify-password', 'UserController@verifyPassword');
Route::post('/save-pin-pattern', 'UserController@savePinPattern');
Route::post('/verify-pin-pattern', 'UserController@verifyPinPattern');
Route::get('/user', 'UserController@getUser');

// balance detail
Route::get('/balance-details', 'UserController@getBalanceDetails');

// credit card
Route::get('/credit-card-tokens', 'UserController@getCreditCardTokens');
Route::post('/credit-card-tokens', 'UserController@storeCreditCardToken');
Route::delete('/credit-card-tokens/{id}', 'UserController@deleteCreditCardToken');

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
Route::get('/orders', 'OrderController@getOrders');
Route::get('/orders/{id}', 'OrderController@getOrderDetails');
Route::post('/use-promo-code', 'OrderController@usePromoCode');
Route::post('/create-order', 'OrderController@createOrder');
Route::post('/save-promo-code', 'OrderController@savePromoCode');
Route::post('/save-payment-method', 'OrderController@savePaymentMethod');
Route::post('/confirm-order', 'OrderController@confirmOrder');
Route::post('/cancel-order', 'OrderController@cancelOrder');


