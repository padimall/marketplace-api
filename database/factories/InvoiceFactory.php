<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Invoice;
use Faker\Generator as Faker;

$factory->define(Invoice::class, function (Faker $faker) {
    return [
        'inv'=>"INV0001",
        'buyer_id'=>1,
        'product_id'=>1,
        'name'=>$faker->firstName(),
        'price'=>"1000",
        'weight'=>"100 gr",
        'quantity'=>"10",
        'description'=>$faker->lastName(),
        'category'=>1,
        'status'=>"1",
    ];
});
