<?php

Route::get('/concerts/{concert}', 'ConcertController@show')->name('concerts.show');
Route::post('/concerts/{concert}/orders', 'ConcertOrdersController@store')->name('orders.store');