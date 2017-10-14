<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

// dashboard
Route::get('/admin', 'AdminController@index');
Route::get('/admin/monthly-statistic', 'AdminController@getMonthlyStatistic');

// users
Route::get('/admin/users', 'AdminController@getUsers');
Route::patch('/admin/users/{user}/status', 'AdminController@updateStatusUser');

// balance details
Route::get('/admin/balance-details', 'AdminController@getBalanceDetails');

// orders
Route::get('/admin/orders', 'AdminController@getOrders');
Route::post('/admin/orders/verify', 'AdminController@verifyOrder');

// products
Route::get('/admin/products', 'AdminController@getProducts');
Route::patch('/admin/products/{product}/status', 'AdminController@updateStatusProduct');

// banks
Route::get('/admin/recipient-banks', 'AdminController@getRecipientBanks');
Route::patch('/admin/recipient-banks/{recipient_bank}/status', 'AdminController@updateStatusRecipientBank');
Route::get('/admin/sender-banks', 'AdminController@getSenderBanks');
Route::patch('/admin/sender-banks/{sender_bank}/status', 'AdminController@updateStatusSenderBank');

