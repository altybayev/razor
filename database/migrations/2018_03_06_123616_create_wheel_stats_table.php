<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWheelStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wheel_stats', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('red')->unsigned();
            $table->integer('black')->unsigned();
            $table->integer('grey')->unsigned();
            $table->integer('white')->unsigned();

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
        Schema::dropIfExists('wheel_stats');
    }
}
