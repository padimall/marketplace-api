<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesProductRatingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices_product_ratings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('invoice_product_id');
            $table->string('name');
            $table->integer('star');
            $table->string('description');
            $table->integer('show_name');
            $table->timestamps();
            $table->foreign('invoice_product_id')->references('id')->on('invoices_products');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoices_product_ratings');
    }
}
