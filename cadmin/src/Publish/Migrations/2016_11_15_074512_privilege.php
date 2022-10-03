<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Privilege extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('privileges', function(Blueprint $table) {
            $table->increments('id');
            $table->string('label');
            $table->timestamps();
        });
        
        Schema::create('module_privileges', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('privilege_id')->unsigned();
            $table->foreign('privilege_id')->references('id')->on('privileges')->onDelete('cascade');
            $table->string('module');
            $table->string('act');
            $table->timestamps();
        });
        
        Schema::table('users', function(Blueprint $table) {
            $table->string('display_name')->after('name');
            $table->enum('is_enable', ['Yes','No'])->default('No')->after('password');
            $table->string('forgot_token')->nullable()->unique()->after('password');
            $table->datetime('forgot_token_expired')->nullable()->after('forgot_token');
            $table->integer('privilege_id')->unsigned()->nullable();
            $table->foreign('privilege_id')->references('id')->on('privileges');
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
        Schema::dropIfExists('module_privileges');
        Schema::dropIfExists('privileges');
    }
}
