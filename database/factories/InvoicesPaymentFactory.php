<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Invoices_payment;
use Faker\Generator as Faker;

$factory->define(Invoices_payment::class, function (Faker $faker) {
    return [
        'invoices_id'=>1,
        'payment_id'=>1,
        'pay_at'=>now(),
        'status'=>1,
    ];
});
