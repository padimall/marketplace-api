<?php

use Illuminate\Database\Seeder;

class InvoicesPaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\Invoices_payment::class,100)->create();
    }
}
