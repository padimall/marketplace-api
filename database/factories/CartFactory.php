<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Cart;
use Faker\Generator as Faker;

$factory->define(Cart::class, function (Faker $faker) {
    return [
        'buyer_id'=>function($faker){
            return App\Buyer::inRandomOrder()->pluck('id')->first();
        },
        'product_id'=>function($faker){
            return App\Product::inRandomOrder()->pluck('id')->first();
        },
        'quantity'=>10,
        'status'=>1
    ];
});
