<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScholarshipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scholarships', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('manager_id')->unsigned();
            $table->bigInteger('scholar_id')->unsigned();
            $table->string('name');
            $table->string('manager_ronin');
            $table->integer('rate');
            $table->text('access_token')->nullable();
            $table->dateTime('updated_token')->nullable();
            $table->text('private_key')->nullable();
            $table->timestamps();
 
            $table->foreign('manager_id')->references('id')->on('users');
            $table->foreign('scholar_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('scholarships');
    }
}
