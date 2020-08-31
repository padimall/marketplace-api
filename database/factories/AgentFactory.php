<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Agent;
use Faker\Generator as Faker;

$factory->define(Agent::class, function (Faker $faker) {
    return [
        'buyer_id'=>function(){
            return App\Buyer::inRandomOrder()->pluck('id')->first();
        },
        'name'=>$faker->firstName(),
        'phone'=>$faker->phoneNumber(),
    ];
});
