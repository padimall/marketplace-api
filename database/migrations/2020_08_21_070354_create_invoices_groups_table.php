<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices_groups', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('amount');
            $table->string('external_payment_id')->nullable();
            $table->uuid('payment_id');
            $table->integer('status');
            $table->timestamps();
            $table->foreign('payment_id')->references('id')->on('payments');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoices_groups');
    }
}
