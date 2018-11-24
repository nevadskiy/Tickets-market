<?php

use App\Concert;
use App\User;
use Carbon\Carbon;
use Faker\Generator as Faker;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(Concert::class, function (Faker $faker) {
    return [
        'title' => 'Example band',
        'user_id' => function () {
            return factory(User::class)->create()->id;
        },
        'subtitle' => 'with The fake Openers',
        'date' => Carbon::parse('+2 weeks'),
        'ticket_price' => 2000,
        'venue' => 'The Example Theatre',
        'venue_address' => '123 Example Lane',
        'city' => 'Fakeville',
        'state' => 'ON',
        'zip' => '17916',
        'additional_information' => 'Some sample additional information'
    ];
});

$factory->state(Concert::class, 'published', function (Faker $faker) {
    return [
        'published_at' => Carbon::parse('-1 week')
    ];
});

$factory->state(Concert::class, 'unpublished', function (Faker $faker) {
    return [
        'published_at' => null,
    ];
});
