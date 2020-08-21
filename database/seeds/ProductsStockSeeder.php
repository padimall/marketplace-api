<?php

use Illuminate\Database\Seeder;

class ProductsStockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\Products_stock::class,1)->create();
    }
}
