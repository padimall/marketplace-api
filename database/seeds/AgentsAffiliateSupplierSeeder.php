<?php

use Illuminate\Database\Seeder;

class AgentsAffiliateSupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\Agents_affiliate_supplier::class,1000)->create();
    }
}
