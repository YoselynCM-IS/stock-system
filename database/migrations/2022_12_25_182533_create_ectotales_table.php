<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEctotalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create('ectotales', function (Blueprint $table) {
        //     $table->bigIncrements('id');
        //     $table->unsignedBigInteger('corte_id');
        //     $table->unsignedBigInteger('editoriale_id');
        //     $table->double('total', 16, 2)->default(0);
        //     $table->double('total_devolucion', 16, 2)->default(0);
        //     $table->double('total_pagos', 16, 2)->default(0);
        //     $table->double('total_pagar', 16, 2)->default(0);
        //     $table->double('total_favor', 16, 2)->default(0);
        //     $table->unsignedBigInteger('corte_id_favor')->default(0);
        //     $table->foreign('corte_id')->references('id')->on('cortes');
        //     $table->foreign('editorial_id')->references('id')->on('editoriales');
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
        Schema::dropIfExists('ectotales');
    }
}
