<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UserSeeder::class);
        // $this->call(BuyerSeeder::class);
        $this->call(SupplierSeeder::class);
        $this->call(AgentSeeder::class);
        $this->call(AgentsAffiliateSupplierSeeder::class);
        $this->call(MainCategorySeeder::class);
        $this->call(ProductsCategorySeeder::class);
        $this->call(PaymentSeeder::class);
        $this->call(LogisticSeeder::class);
        $this->call(ProductSeeder::class);
        $this->call(ProductsImageSeeder::class);
        $this->call(CartSeeder::class);
        $this->call(CheckoutSeeder::class);
        $this->call(CheckoutPaymentSeeder::class);
        $this->call(CheckoutLogisticSeeder::class);
        $this->call(InvoiceSeeder::class);
        $this->call(InvoicesProductSeeder::class);
        $this->call(InvoicesAgentSeeder::class);
        $this->call(InvoicesPaymentSeeder::class);
        $this->call(InvoicesLogisticSeeder::class);


    }
}
