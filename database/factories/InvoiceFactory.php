<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Invoice;
use Faker\Generator as Faker;

$factory->define(Invoice::class, function (Faker $faker) {
    return [
        'inv'=>"INV0001",
        'buyer_id'=>function(){
            return App\Buyer::inRandomOrder()->pluck('id')->first();
        },
        'product_id'=>function(){
            return App\Product::inRandomOrder()->pluck('id')->first();
        },
        'name'=>$faker->firstName(),
        'price'=>"1000",
        'weight'=>"100 gr",
        'quantity'=>"10",
        'description'=>$faker->lastName(),
        'category'=>function(){
            return App\Products_category::inRandomOrder()->pluck('id')->first();
        },
        'status'=>"1",
    ];
});
