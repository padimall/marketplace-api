<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Invoices_payment;
use Faker\Generator as Faker;

$factory->define(Invoices_payment::class, function (Faker $faker) {
    return [
        'invoices_id'=>function(){
            return App\Invoice::inRandomOrder()->pluck('id')->first();
        },
        'payment_id'=>function(){
            return App\Payment::inRandomOrder()->pluck('id')->first();
        },
        'pay_at'=>now(),
        'status'=>1,
    ];
});
