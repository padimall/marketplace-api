<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Products_stock;
use Faker\Generator as Faker;

$factory->define(Products_stock::class, function (Faker $faker) {
    return [
        'product_id'=>1,
        'stock'=>"10",
    ];
});
