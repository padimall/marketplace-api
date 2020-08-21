<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Agent;
use Faker\Generator as Faker;

$factory->define(Agent::class, function (Faker $faker) {
    return [
        'buyer_id'=>1,
        'name'=>$faker->firstName(),
        'phone'=>$faker->phoneNumber(),
    ];
});
