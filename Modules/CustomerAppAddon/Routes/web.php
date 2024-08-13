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

use Illuminate\Support\Facades\Route;

if (\Illuminate\Support\Facades\Schema::hasTable('translations') && check_module('localization')) {
    Route::group(
        [
            'prefix' => LaravelLocalization::setLocale(),
            'middleware' => [ 'localeSessionRedirect', 'localizationRedirect', 'localeViewPath' ]
        ], function(){

        Route::middleware('auth')->prefix(env('PREFIX_ADMIN', 'admin'))->group(function() {

                Route::get('/customer-app-settings', 'CustomerAppAddonController@index')->name('customerappaddon-settings');
                Route::post('/send-customer-app-info', 'CustomerAppAddonController@sendCustomerAppInfo')->name('send.customer.app.info');

        });
    });
}else{

    Route::middleware('auth')->prefix(env('PREFIX_ADMIN', 'admin'))->group(function() {

            Route::get('/customer-app-settings', 'CustomerAppAddonController@index')->name('customer-app-settings');
            Route::post('/send-customer-app-info', 'CustomerAppAddonController@sendCustomerAppInfo')->name('send.customer.app.info');

    });
}
