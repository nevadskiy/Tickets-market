<?php

Route::get('/concerts/{concert}', 'ConcertController@show')->name('concerts.show');
