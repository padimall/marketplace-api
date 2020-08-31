<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCheckoutPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('checkout_payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('checkout_id');
            $table->uuid('payment_type');
            $table->timestamps();
            $table->foreign('checkout_id')->references('id')->on('checkouts');
            $table->foreign('payment_type')->references('id')->on('payments');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('checkout_payments');
    }
}
