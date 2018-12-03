<?php

Route::get('concerts/{concert}', 'ConcertController@show')->name('concerts.show');
Route::post('concerts/{concert}/orders', 'ConcertOrdersController@store')->name('orders.store');
Route::get('orders/{confirmationNumber}', 'OrdersController@show');

Route::get('login', 'Auth\LoginController@show')->name('login');
Route::post('login', 'Auth\LoginController@login');

Route::get('invitations/{code}', 'InvitationsController@show')->name('invitations.show');
Route::post('register', 'Auth\RegisterController@register')->name('auth.register');

Route::group([
    'middleware' => 'auth',
    'prefix' => 'backstage',
    'as' => 'backstage.',
    'namespace' => 'Backstage',
], function () {
    Route::get('concerts', 'ConcertsController@index')->name('concerts.index');
    Route::get('concerts/create', 'ConcertsController@create')->name('concerts.create');
    Route::get('concerts/{concert}/edit', 'ConcertsController@edit')->name('concerts.edit');
    Route::post('concerts', 'ConcertsController@store')->name('concerts.store');
    Route::put('concerts/{id}', 'ConcertsController@update')->name('concerts.update');

    Route::post('published-concerts', 'PublishedConcertsController@store')->name('published-concerts.store');
    Route::get('published-concerts/{id}/orders', 'PublishedConcertOrdersController@index')->name('published-concert-orders.index');

    Route::get('concerts/{id}/messages/create', 'ConcertMessagesController@create')->name('concert-messages.create');
    Route::post('concerts/{id}/messages', 'ConcertMessagesController@store')->name('concert-messages.store');
});

