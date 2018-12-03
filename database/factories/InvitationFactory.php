<?php

use Faker\Generator as Faker;

$factory->define(App\Invitation::class, function (Faker $faker) {
    return [
        'email' => 'john@example.com',
        'code' => 'TESTCODE1234',
    ];
});
