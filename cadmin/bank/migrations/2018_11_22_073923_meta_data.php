<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MetaData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
		Schema::create('meta_datas',function(Blueprint $table) {
			$table->bigIncrements('id');
			$table->string('lang');
			$table->string('module');
			$table->string('uniqid');
            $table->unique(['module','uniqid','lang']);
			$table->text('meta_title')->nullable();
			$table->text('meta_keywords')->nullable();
			$table->text('meta_description')->nullable();
			$table->text('meta_image')->nullable();
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
		Schema::dropIfExists('meta_datas');
    }
}
