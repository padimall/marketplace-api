<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Cart;
use Faker\Generator as Faker;

$factory->define(Cart::class, function (Faker $faker) {
    return [
        'buyer_id'=>1,
        'product_id'=>1,
        'status'=>1,
    ];
});
