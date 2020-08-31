<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Checkout;
use Faker\Generator as Faker;

$factory->define(Checkout::class, function (Faker $faker) {
    return [
        'product_id'=>function($faker){
            return App\Product::inRandomOrder()->pluck('id')->first();
        },
        'buyer_id'=>function($faker){
            return App\Buyer::inRandomOrder()->pluck('id')->first();
        },
        'name'=>$faker->firstName(),
        'price'=>"1000",
        'weight'=>"100 gr",
        'quantity'=>"10",
        'status'=>1,
    ];
});
