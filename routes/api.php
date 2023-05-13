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
        Route::post(''      , 'ClientController@store');
        Route::put('{id}'   , 'ClientController@update');
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


    /*
    |--------------------------------------------------------------------------
    | CATEGORIAS
    |--------------------------------------------------------------------------
    */

    Route::group(['prefix' => 'categories'], function () {
        Route::get(''       , 'CategoryController@index');
        Route::get('{id}'   , 'CategoryController@show');
        Route::post(''      , 'CategoryController@store');
        Route::put('{id}'   , 'CategoryController@update');
        Route::delete('{id}', 'CategoryController@destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | PRODUTOS
    |--------------------------------------------------------------------------
    */

    Route::group(['prefix' => 'products'], function () {
        Route::get(''       , 'ProductController@index');
        Route::get('{id}'   , 'ProductController@show');
        Route::post(''      , 'ProductController@store');
        Route::put('{id}'   , 'ProductController@update');
        Route::delete('{id}', 'ProductController@destroy');
    });

});
