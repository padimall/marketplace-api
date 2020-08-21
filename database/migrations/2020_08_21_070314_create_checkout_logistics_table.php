<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCheckoutLogisticsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('checkout_logistics', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('checkout_id');
            $table->unsignedInteger('logistic_type');
            $table->timestamps();
            $table->foreign('checkout_id')->references('id')->on('checkouts');
            $table->foreign('logistic_type')->references('id')->on('logistics');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('checkout_logistics');
    }
}
