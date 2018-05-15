<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDiceDuleLoggerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dice_duel_logger', function (Blueprint $table) {
            $table->increments('id');

            $table->string('played_at')->unique()->nullable();
            $table->timestamp('logged_at')->nullable();
            $table->integer('match_id')->nullable();
            $table->integer('dice_1')->unsigned();
            $table->integer('dice_2')->unsigned();

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
        Schema::dropIfExists('dice_duel_logger');
    }
}
