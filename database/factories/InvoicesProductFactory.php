<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Invoices_product;
use Faker\Generator as Faker;

$factory->define(Invoices_product::class, function (Faker $faker) {
    return [
        'invoice_id'=>function(){
            return App\Invoice::inRandomOrder()->pluck('id')->first();
        },
        'product_id'=>function(){
            return App\Product::inRandomOrder()->pluck('id')->first();
        },
        'name'=>$faker->firstName(),
        'price'=>1000,
        'quantity'=>10
    ];
});
