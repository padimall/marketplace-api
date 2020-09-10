<?php

use Illuminate\Database\Seeder;

class InvoicesProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\Invoices_product::class,50)->create();
    }
}
