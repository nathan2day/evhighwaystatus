<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConnectorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
            Schema::create('connectors', function (Blueprint $table) {
            $table->increments('id')->unsigned()->index();
            $table->integer('charger_id')->unsigned()->index();          
            $table->decimal('power', 5, 2)->unsigned();
            $table->string('status');
            $table->integer('position')->unsigned();
            $table->integer('quantity')->unsigned();
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
        Schema::dropIfExists('connectors');
    }
}
