<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Agents_affiliate_supplier;
use Faker\Generator as Faker;

$factory->define(Agents_affiliate_supplier::class, function (Faker $faker) {
    return [
        'supplier_id'=>1,
        'agent_id'=>1,
    ];
});
