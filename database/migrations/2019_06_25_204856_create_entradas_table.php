<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEntradasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create('entradas', function (Blueprint $table) {
        //     $table->bigIncrements('id');
        //     $table->string('folio', 50)->unique();
        //     $table->string('editorial')->nullable();
        //     $table->integer('unidades')->default(0);
        //     $table->double('total', 16, 2)->default(0);
        //     $table->double('total_devolucion', 16, 2)->default(0);
        //     $table->double('total_pagos', 16, 2)->default(0);
        //     $table->timestamps();
        // }); 

        // Schema::create('registros', function (Blueprint $table) {
        //     $table->bigIncrements('id');
        //     $table->unsignedBigInteger('entrada_id')->nullable();
        //     $table->foreign('entrada_id')->references('id')->on('entradas');
        //     $table->unsignedBigInteger('libro_id')->nullable();
        //     $table->foreign('libro_id')->references('id')->on('libros');
        //     $table->float('costo_unitario', 8, 2)->default(0);
        //     $table->integer('unidades')->default(0);
        //     $table->double('total', 16, 2)->default(0);
        //     $table->integer('unidades_base')->default(0);
        //     $table->double('total_base', 16, 2)->default(0);
        //     $table->softDeletes(); //Nueva línea, para el borrado lógico
        //     $table->timestamps();
        // });

        // Schema::create('repayments', function (Blueprint $table) {
        //     $table->bigIncrements('id');
        //     $table->unsignedBigInteger('entrada_id');
        //     $table->foreign('entrada_id')->references('id')->on('entradas');
        //     $table->double('pago', 16, 2)->default(0);
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
        Schema::dropIfExists('entradas');
        Schema::dropIfExists('registros');
    }
}
