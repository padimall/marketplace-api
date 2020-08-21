<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Checkout_payment;
use Faker\Generator as Faker;

$factory->define(Checkout_payment::class, function (Faker $faker) {
    return [
        'checkout_id'=>1,
        'payment_type'=>1,
    ];
});
