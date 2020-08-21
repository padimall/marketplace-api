<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Supplier;
use Faker\Generator as Faker;

$factory->define(Supplier::class, function (Faker $faker) {
    return [
        'buyer_id'=>1,
        'name'=>$faker->firstName(),
        'phone'=>$faker->phoneNumber(),
    ];
});
