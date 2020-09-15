<?php

use Illuminate\Database\Seeder;

class CheckoutLogisticSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\Checkout_logistic::class,1000)->create();
    }
}
