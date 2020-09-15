<?php

use Illuminate\Database\Seeder;

class InvoicesLogisticSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\Invoices_logistic::class,1000)->create();
    }
}
