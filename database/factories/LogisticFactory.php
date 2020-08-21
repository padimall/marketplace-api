<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Logistic;
use Faker\Generator as Faker;

$factory->define(Logistic::class, function (Faker $faker) {
    return [
        'name'=>$faker->firstName(),
    ];
});
