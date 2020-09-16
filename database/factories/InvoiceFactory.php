<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Invoice;
use Faker\Generator as Faker;

$factory->define(Invoice::class, function (Faker $faker) {
    return [
        'user_id'=>function(){
            return App\User::inRandomOrder()->pluck('id')->first();
        },
        'supplier_id'=>function(){
            return App\Supplier::inRandomOrder()->pluck('id')->first();
        },
        'amount'=>10000,
        'status'=>"1",
    ];
});
