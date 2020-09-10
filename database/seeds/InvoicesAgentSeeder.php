<?php

use Illuminate\Database\Seeder;

class InvoicesAgentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\Invoices_agent::class,50)->create();
    }
}
