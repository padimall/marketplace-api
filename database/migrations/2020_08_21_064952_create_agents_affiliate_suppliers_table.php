<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAgentsAffiliateSuppliersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agents_affiliate_suppliers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('supplier_id');
            $table->uuid('agent_id');
            $table->timestamps();
            $table->foreign('supplier_id')->references('id')->on('suppliers');
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
        Schema::dropIfExists('agents_affiliate_suppliers');
    }
}
