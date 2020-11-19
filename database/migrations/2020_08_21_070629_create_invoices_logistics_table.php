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
            $table->uuid('id')->primary();
            $table->uuid('invoices_id');
            $table->uuid('logistic_id');
            $table->string('resi');
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
