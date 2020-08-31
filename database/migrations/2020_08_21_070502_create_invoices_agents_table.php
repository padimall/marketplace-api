<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesAgentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices_agents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('invoices_id');
            $table->string('invoice_status');
            $table->uuid('agent_id');
            $table->integer('status');
            $table->timestamps();
            $table->foreign('invoices_id')->references('id')->on('invoices');
            $table->foreign('agent_id')->references('id')->on('agents');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoices_agents');
    }
}
