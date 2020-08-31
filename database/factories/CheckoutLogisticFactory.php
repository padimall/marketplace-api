<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Checkout_logistic;
use Faker\Generator as Faker;

$factory->define(Checkout_logistic::class, function (Faker $faker) {
    return [
        'checkout_id'=>function(){
            return App\Checkout::inRandomOrder()->pluck('id')->first();
        },
        'logistic_type'=>function(){
            return App\Logistic::inRandomOrder()->pluck('id')->first();
        },
    ];
});
