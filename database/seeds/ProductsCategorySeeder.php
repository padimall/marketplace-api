<?php

use Illuminate\Database\Seeder;

class ProductsCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\Products_category::class,100)->create();
    }
}
