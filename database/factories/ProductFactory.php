<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Product;
use Faker\Generator as Faker;

$factory->define(Product::class, function (Faker $faker) {
    return [
        'agent_id'=>1,
        'name'=>$faker->firstName(),
        'price'=>"1000",
        'weight'=>"100 gr",
        'description'=>$faker->lastName(),
        'category'=>1,
        'status'=>"1",
    ];
});
