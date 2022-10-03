<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Logs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
		Schema::create('logs', function(Blueprint $table) {
			$table->bigIncrements('id');
            $table->integer('user_id')->unsigned();
            $table->string('username')->nullable();
            $table->string('user_display_name')->nullable();
            $table->string('module')->nullable();
            $table->string('act')->nullable();
            $table->string('unique_id')->nullable();
            $table->string('unique_label')->nullable();
            $table->text('post')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
		});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::dropIfExists('logs');
    }
}
