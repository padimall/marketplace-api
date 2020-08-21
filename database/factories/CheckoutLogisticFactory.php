<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Checkout_logistic;
use Faker\Generator as Faker;

$factory->define(Checkout_logistic::class, function (Faker $faker) {
    return [
        'checkout_id'=>1,
        'logistic_type'=>1,
    ];
});
