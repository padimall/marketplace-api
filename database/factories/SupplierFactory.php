<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Supplier;
use Faker\Generator as Faker;

$factory->define(Supplier::class, function (Faker $faker) {
    return [
        'buyer_id'=>function(){
            return App\Buyer::inRandomOrder()->pluck('id')->first();
        },
        'name'=>$faker->firstName(),
        'phone'=>$faker->phoneNumber(),
    ];
});
