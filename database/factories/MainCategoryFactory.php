<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Main_category;
use Faker\Generator as Faker;

$factory->define(Main_category::class, function (Faker $faker) {
    return [
        'name'=>$faker->firstName(),
        'image'=>'main-category/image.jpg',
        'status'=>1,
    ];
});
