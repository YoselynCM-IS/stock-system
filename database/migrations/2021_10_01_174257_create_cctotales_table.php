<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCctotalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create('cctotales', function (Blueprint $table) {
        //     $table->bigIncrements('id');
        //     $table->unsignedBigInteger('corte_id');
        //     $table->unsignedBigInteger('cliente_id');
        //     $table->double('total', 16, 2)->default(0);
        //     $table->double('total_devolucion', 16, 2)->default(0);
        //     $table->double('total_pagos', 16, 2)->default(0);
        //     $table->double('total_pagar', 16, 2)->default(0);
        //     $table->double('total_favor', 16, 2)->default(0);
        //     $table->unsignedBigInteger('corte_id_favor')->default(0);
        //     $table->foreign('corte_id')->references('id')->on('cortes');
        //     $table->foreign('cliente_id')->references('id')->on('clientes');
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
        Schema::dropIfExists('cctotales');
    }
}
