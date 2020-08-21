<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Invoices_agent;
use Faker\Generator as Faker;

$factory->define(Invoices_agent::class, function (Faker $faker) {
    return [
        'invoices_id'=>1,
        'invoice_status'=>"INV status",
        'agent_id'=>1,
        'status'=>1,
    ];
});
