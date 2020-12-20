<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceProductRatingImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_product_rating_images', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('invoice_product_rating_id');
            $table->string('image');
            $table->timestamps();
            $table->foreign('invoice_product_rating_id')->references('id')->on('invoices_product_ratings');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoice_product_rating_images');
    }
}
