<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Drafts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::create('drafts',function(Blueprint $table) {
			$table->bigIncrements('id');
			$table->string('uniqid');
			$table->string('module');
			$table->string('lang')->nullable();
			$table->string('draft_key');
			//$table->unique(['uniqid','module','lang','draft_key']);
			$table->text('draft_value')->nullable();
			$table->timestamps();
		});
		
		Schema::create('draft_logs',function(Blueprint $table) {
			$table->bigIncrements('id');
			$table->string('uniqid');
			$table->string('module');
			$table->unique(['uniqid','module']);
            $table->text('title')->nullable();
            $table->integer('status')->default(0);
            $table->string('draft_by')->nullable();
            $table->string('approve_by')->nullable();
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
		Schema::dropIfExists('drafts');
		Schema::dropIfExists('draft_logs');
    }
}