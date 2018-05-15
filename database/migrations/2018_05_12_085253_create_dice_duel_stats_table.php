<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDiceDuelStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dice_duel_stats', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('red_equals_blue')->unsigned();
            $table->integer('red_plus_blue_greater_ten')->unsigned();
            $table->integer('red_plus_blue_less_four')->unsigned();
            $table->integer('red_plus_blue_equals_seven')->unsigned();
            $table->integer('red_greater_blue')->unsigned();
            $table->integer('blue_greated_red')->unsigned();
            $table->integer('red_plus_blue_equals_six')->unsigned();
            $table->integer('red_plus_blue_equals_eight')->unsigned();

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
        Schema::dropIfExists('dice_duel_stats');
    }
}
