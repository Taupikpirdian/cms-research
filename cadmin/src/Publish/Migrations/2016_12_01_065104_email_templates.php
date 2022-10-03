<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Cactuar\Admin\CactuarBlueprint;

class EmailTemplates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        CactuarBlueprint::schema()->create('email_templates', function (Blueprint $table) {
            $table->increments('id');
			$table->string('purpose')->unique();
			$table->string('type');
			$table->string('email_admin')->nullable();
            $table->string('cc')->nullable();
            $table->string('bcc')->nullable();
			$table->string('subject_admin')->nullable();
			$table->text('body_admin');
			$table->timestamps();
        });
        
        CactuarBlueprint::schema()->create('email_templates', function (Blueprint $table) {
            $table->translated();
			$table->string('subject_end_user')->nullable();
			$table->text('body_end_user');
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
