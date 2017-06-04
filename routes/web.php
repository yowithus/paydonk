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


Route::get('/admin', 'AdminController@index');
Route::get('/admin/users', 'AdminController@getUsers');
Route::get('/admin/deposit-details', 'AdminController@getDepositDetails');
Route::get('/admin/topup-orders', 'AdminController@getTopUpOrders');
Route::post('/admin/topup-orders/verify', 'AdminController@verifyTopUpOrder');