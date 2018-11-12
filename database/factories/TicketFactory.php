<?php

use App\Concert;
use App\Ticket;
use Faker\Generator as Faker;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(Ticket::class, function (Faker $faker) {
    return [
        'concert_id' => function () {
            return factory(Concert::class)->create()->id;
        }
    ];
});
