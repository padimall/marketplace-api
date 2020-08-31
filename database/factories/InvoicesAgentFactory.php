<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Invoices_agent;
use Faker\Generator as Faker;

$factory->define(Invoices_agent::class, function (Faker $faker) {
    return [
        'invoices_id'=>function(){
            return App\Invoice::inRandomOrder()->pluck('id')->first();
        },
        'invoice_status'=>"INV status",
        'agent_id'=>function(){
            return App\Agent::inRandomOrder()->pluck('id')->first();
        },
        'status'=>1,
    ];
});
