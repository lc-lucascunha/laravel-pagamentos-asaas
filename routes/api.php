<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Api'], function () {

    /*
    |--------------------------------------------------------------------------
    | CLIENTES
    |--------------------------------------------------------------------------
    */

    Route::group(['prefix' => 'clients'], function () {
        Route::post(''            , 'ClientController@store');
        Route::put('{id}'         , 'ClientController@update');
        Route::get('{id}/cards'   , 'ClientController@cards');
        Route::get('{id}/payments', 'ClientController@payments');
    });

    /*
    |--------------------------------------------------------------------------
    | PAGAMENTOS
    |--------------------------------------------------------------------------
    */

    Route::group(['prefix' => 'payments'], function () {
        Route::get('{id}', 'PaymentController@show');
        Route::post('', 'PaymentController@store');
    });

});
