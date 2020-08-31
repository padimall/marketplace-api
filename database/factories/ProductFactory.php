<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Product;
use Faker\Generator as Faker;

$factory->define(Product::class, function (Faker $faker) {
    return [
        'supplier_id'=>function(){
            return App\Supplier::inRandomOrder()->pluck('id')->first();
        },
        'name'=>$faker->firstName(),
        'price'=>"1000",
        'weight'=>"100 gr",
        'description'=>$faker->lastName(),
        'category'=>function(){
            return App\Products_category::inRandomOrder()->pluck('id')->first();
        },
        'stock'=>100,
        'status'=>"1",
    ];
});
