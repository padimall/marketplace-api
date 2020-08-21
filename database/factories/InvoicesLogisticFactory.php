<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Invoices_logistic;
use Faker\Generator as Faker;

$factory->define(Invoices_logistic::class, function (Faker $faker) {
    return [
        'invoices_id'=>1,
        'logistic_id'=>1,
        'status'=>1,
    ];
});
