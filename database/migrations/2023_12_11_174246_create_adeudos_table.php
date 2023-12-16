<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create('adeudos', function (Blueprint $table) {
        //     $table->bigIncrements('id');
        //     $table->unsignedBigInteger('cliente_id');
        //     $table->foreign('cliente_id')->references('id')->on('clientes');
        //     $table->unsignedBigInteger('corte_id');
        //     $table->foreign('corte_id')->references('id')->on('cortes');
        //     $table->unsignedBigInteger('remdeposito_id');
        //     $table->foreign('remdeposito_id')->references('id')->on('remdepositos');
        //     $table->double('saldo_inicial', 16, 2)->default(0);
        //     $table->double('saldo_pagado', 16, 2)->default(0);
        //     $table->double('saldo_pendiente', 16, 2)->default(0);
        //     $table->integer('dias')->default(0);
        //     $table->enum('rango', ['0-29', '30-59', '60-89', '90-119', '120-149', '+150'])->default('0-29');
        //     $table->string('ingresado_por');
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
        Schema::dropIfExists('adeudos');
    }
};
