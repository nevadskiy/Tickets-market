<?php

Route::get('/concerts/{concert}', 'ConcertController@show')->name('concerts.show');
Route::post('/concerts/{concert}/orders', 'ConcertOrdersController@store')->name('orders.store');
Route::get('/orders/{confirmationNumber}', 'OrdersController@show');

Route::post('/login', 'Auth\LoginController@login');
