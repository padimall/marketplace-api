<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesLogisticsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices_logistics', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('invoices_id');
            $table->unsignedInteger('logistic_id');
            $table->integer('status');
            $table->timestamps();
            $table->foreign('invoices_id')->references('id')->on('invoices');
            $table->foreign('logistic_id')->references('id')->on('logistics');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoices_logistics');
    }
}
