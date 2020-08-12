<?php

use Illuminate\Database\Seeder;
use App\Buyer;

class BuyerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(Buyer::class,20)->create();
    }
}
