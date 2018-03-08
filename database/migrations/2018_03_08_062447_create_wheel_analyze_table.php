<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWheelAnalyzeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wheel_analyze', function (Blueprint $table) {
            $table->increments('id');

            $table->string('played_at')->unique()->nullable();
            $table->timestamp('logged_at')->nullable();
            $table->integer('match_id')->nullable();
            $table->integer('number')->unsigned();
            $table->string('color')->nullable();

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
        Schema::dropIfExists('wheel_analyze');
    }
}
