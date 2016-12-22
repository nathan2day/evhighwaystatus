<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChargerConnectorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('charger_connector', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('charger_id')->unsigned();
            $table->integer('connector_id')->unsigned();
            $table->integer('position');
            $table->string('status');
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
        Schema::dropIfExists('charger_connector');
    }
}
