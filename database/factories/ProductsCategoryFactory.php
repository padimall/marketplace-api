<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Products_category;
use Faker\Generator as Faker;

$factory->define(Products_category::class, function (Faker $faker) {
    return [
        'name'=>$faker->firstName(),
        'image'=>'image.png',
        'status'=>1,
    ];
});
