<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Checkout_payment;
use Faker\Generator as Faker;

$factory->define(Checkout_payment::class, function (Faker $faker) {
    return [
        'checkout_id'=>function(){
            return App\Checkout::inRandomOrder()->pluck('id')->first();
        },
        'payment_type'=>function(){
            return App\Payment::inRandomOrder()->pluck('id')->first();
        },
    ];
});
