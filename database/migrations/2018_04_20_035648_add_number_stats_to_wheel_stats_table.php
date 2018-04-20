<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNumberStatsToWheelStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wheel_stats_numbers', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('one_six')->unsigned();
            $table->integer('seven_twelve')->unsigned();
            $table->integer('thirteen_eighteen')->unsigned();

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
        Schema::dropIfExists('wheel_stats_numbers');
    }
}
