<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRootUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
		DB::table('users')->insert([
			'id' => 1,
			'name' => 'root',
			'display_name' => 'root',
			'email' => 'root@root.com',
			'privilege_id' => null,
			'password' => \Hash::make('root123'),
			'is_enable' => 'Yes',
			'remember_token' => 'TaTUUFDLCHsFNQO99RLr2Cr7zbha4dav78EcsWVHcmuOvwV5QUsP2qkt3ogQ',
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        DB::table('users')->insert([
			'id' => 2,
			'name' => 'superadmin',
			'display_name' => 'superadmin',
			'email' => 'superadmin@webadmin.com',
			'privilege_id' => null,
			'password' => \Hash::make('superadmin123'),
			'is_enable' => 'Yes',
			'remember_token' => 'TaTUUFDLCHsFNQO99RLr2Cr7zbha4dav78EcsWVHcmuOvwV5QUsP2qkt3ogQ',
			'created_at' => date('Y-m-d H:i:s'),
			'updated_at' => date('Y-m-d H:i:s')
		]);
        
        DB::table('user_password_histories')->insert([
            'user_id' => 1,
            'password' => \Hash::make('root123'),
            'action' => 'migration',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        DB::table('user_password_histories')->insert([
            'user_id' => 2,
            'password' => \Hash::make('superadmin123'),
            'action' => 'migration',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
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
