<?php

Route::get('concerts/{concert}', 'ConcertController@show')->name('concerts.show');
Route::post('concerts/{concert}/orders', 'ConcertOrdersController@store')->name('orders.store');
Route::get('orders/{confirmationNumber}', 'OrdersController@show');

Route::get('login', 'Auth\LoginController@show')->name('login');
Route::post('login', 'Auth\LoginController@login');

Route::group([
    'middleware' => 'auth',
    'prefix' => 'backstage',
    'namespace' => 'Backstage'
], function () {
    Route::get('concerts/create', 'ConcertsController@create');
    Route::post('concerts', 'ConcertsController@store');
});

