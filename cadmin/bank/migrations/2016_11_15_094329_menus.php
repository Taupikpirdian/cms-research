<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Cactuar\Admin\CactuarBlueprint;

class Menus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        CactuarBlueprint::schema()->create('menus', function (CactuarBlueprint $table) {
            $table->increments('id');
			$table->integer('parent_id')->default(0);
			$table->string('type')->nullable();
			//$table->string('meta_image')->nullable(); //menjadi table terpisah
            $table->sortable();
            $table->publishable();
            $table->timestamps();
        });
        
        CactuarBlueprint::schema()->create('menus', function (CactuarBlueprint $table) {
            $table->translated();
            $table->string('label');
            $table->string('url')->nullable();
            //$table->string('meta_title')->nullable();
            //$table->string('meta_keywords')->nullable();
			//$table->string('meta_description')->nullable();
			//$table->text('searchable')->nullable();
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
