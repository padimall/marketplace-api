<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Buyer;
use Faker\Generator as Faker;

$factory->define(Buyer::class, function (Faker $faker) {
    return [
        'username'=>$faker->firstName(),
        'password'=>hash('sha256','112'),
        'email'=>$faker->safeEmail(),
        'address'=>$faker->address(),
        'phone'=>$faker->phoneNumber(),
    ];
});
