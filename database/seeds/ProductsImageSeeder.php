<?php

use Illuminate\Database\Seeder;

class ProductsImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\Products_image::class,100)->create();
    }
}
