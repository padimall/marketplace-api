<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Invoices_logistic;
use Faker\Generator as Faker;

$factory->define(Invoices_logistic::class, function (Faker $faker) {
    return [
        'invoices_id'=>function(){
            return App\Invoice::inRandomOrder()->pluck('id')->first();
        },
        'logistic_id'=>function(){
            return App\Logistic::inRandomOrder()->pluck('id')->first();
        },
        'status'=>1,
    ];
});
