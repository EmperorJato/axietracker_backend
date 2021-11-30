<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDailyScholarship extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('daily_scholarship', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('scholarship_id')->unsigned();
            $table->integer('slp');
            $table->integer('slp_inventory');
            $table->integer('pvp');
            $table->integer('draw');
            $table->integer('pvp_total'); 
            $table->integer('energy');
            $table->tinyInteger('reward');
            $table->integer('mmr');
            $table->dateTime('datetime');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('scholarship_id')->references('id')->on('scholarships');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('daily_scholarship');
    }
}
