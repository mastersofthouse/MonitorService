<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMonitoringTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create("monitoring", function (Blueprint $table){
            $table->uuid('id')->primary();
            $table->uuid('uuid')->nullable();
            $table->uuid('batch_uuid')->nullable();
            $table->json('authentication')->nullable();
            $table->json('tenant')->nullable();
            $table->string('type')->nullable();
            $table->string('hostname')->nullable();
            $table->json('context')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('monitoring');
    }
}
