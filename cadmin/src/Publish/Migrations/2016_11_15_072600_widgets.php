<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Widgets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('widgets', function(Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('uniqid');
            $table->string('module');
            $table->string('key');
            $table->bigInteger('parent_id')->nullable();
            $table->timestamps();
        });
        
        Schema::create('widget_detail', function(Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('widget_id')->unsigned();
            $table->string('lang');
            $table->string('field_name');
            $table->unique(['widget_id','lang','field_name']);
            $table->text('val');
            $table->foreign('widget_id')->references('id')->on('widgets')->onDelete('cascade');
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
        Schema::dropIfExists('widget_detail');
        Schema::dropIfExists('widgets');
    }
}
