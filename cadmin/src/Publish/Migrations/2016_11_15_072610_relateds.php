<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Relateds extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('relateds', function(Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('uniqid');
            $table->string('module');
            $table->string('related');
            $table->string('related_uniqid');
            //$table->unique(['uniqid','module','related','related_uniqid']); * too long unique key
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
        Schema::dropIfExists('relateds');
    }
}
