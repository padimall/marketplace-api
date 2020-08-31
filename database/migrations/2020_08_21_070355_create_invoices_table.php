<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('inv');
            $table->uuid('buyer_id');
            $table->uuid('product_id');
            $table->string('name');
            $table->string('price');
            $table->string('weight');
            $table->string('quantity');
            $table->string('description');
            $table->uuid('category');
            $table->string('status');
            $table->timestamps();
            $table->foreign('buyer_id')->references('id')->on('buyers');
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('category')->references('id')->on('products_categories');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoices');
    }
}
