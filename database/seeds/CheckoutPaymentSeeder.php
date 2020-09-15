<?php

use Illuminate\Database\Seeder;

class CheckoutPaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\Checkout_payment::class,1000)->create();
    }
}
