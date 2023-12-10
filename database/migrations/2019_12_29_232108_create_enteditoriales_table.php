<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEnteditorialesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create('enteditoriales', function (Blueprint $table) {
        //     $table->bigIncrements('id');
        //     $table->string('editorial');
        //     $table->double('total', 16, 2)->default(0);
        //     $table->double('total_devolucion', 16, 2)->default(0);
        //     $table->double('total_pagos', 16, 2)->default(0);
        //     $table->double('total_pendiente', 16, 2)->default(0);
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
        Schema::dropIfExists('enteditoriales');
    }
}
