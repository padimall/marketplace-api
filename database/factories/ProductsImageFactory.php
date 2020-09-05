<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Products_image;
use Faker\Generator as Faker;

$factory->define(Products_image::class, function (Faker $faker) {
    return [
        'product_id'=>function(){
            return App\Product::inRandomOrder()->pluck('id')->first();
        },
        'image'=>'product/image.jpg',
    ];
});
