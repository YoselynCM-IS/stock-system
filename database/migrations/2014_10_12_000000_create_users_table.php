<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create('roles', function (Blueprint $table) {
        //     $table->bigIncrements('id');
        //     $table->string('rol');
        //     $table->timestamps();
        // });

        // Schema::create('users', function (Blueprint $table) {
        //     $table->bigIncrements('id');
        //     $table->unsignedBigInteger('role_id');
        //     $table->foreign('role_id')->references('id')->on('roles');
        //     $table->string('name', 80)->unique();
        //     $table->string('user_name', 50)->unique();
        //     $table->string('email', 50)->unique();
        //     $table->string('password');
        //     $table->rememberToken();
        //     $table->timestamps(); 
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('roles');
    }
}
