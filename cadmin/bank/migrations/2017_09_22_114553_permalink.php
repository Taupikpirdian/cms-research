<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Permalink extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('permalinks', function(Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('module');
            $table->string('uniqid');
            $table->string('lang');
            $table->unique(['module','uniqid','lang']);
            $table->string('permalink')->unique();
            $table->string('label')->nullable();
            $table->text('description')->nullable();
            $table->longText('searchable')->nullable();
            $table->integer('is_active')->default(0);
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
        //
    }
}
