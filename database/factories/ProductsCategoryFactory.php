<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Products_category;
use Faker\Generator as Faker;

$factory->define(Products_category::class, function (Faker $faker) {
    return [
        'name'=>$faker->firstName(),
        'image'=>'product-category/image.jpg',
        'status'=>1,
        'main_category_id'=>function(){
            return App\Main_category::inRandomOrder()->pluck('id')->first();
        },
    ];
});
