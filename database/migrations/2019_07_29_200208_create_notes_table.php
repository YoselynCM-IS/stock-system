<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create('notes', function (Blueprint $table) {
        //     $table->bigIncrements('id');
        //     $table->string('folio');
        //     $table->string('cliente');
        //     $table->double('total_salida', 16, 2)->default(0);
        //     $table->double('total_devolucion', 16, 2)->default(0);
        //     $table->double('total_pagar', 16, 2)->default(0);
        //     $table->double('pagos', 16, 2)->default(0);
        //     $table->date('fecha_devolucion')->nullable();
        //     $table->text('entregado_por')->nullable();
        //     $table->timestamps();
        // }); 

        // Schema::create('registers', function (Blueprint $table) {
        //     $table->bigIncrements('id');
        //     $table->unsignedBigInteger('note_id')->nullable();
        //     $table->foreign('note_id')->references('id')->on('notes');
        //     $table->unsignedBigInteger('libro_id')->nullable();
        //     $table->foreign('libro_id')->references('id')->on('libros');
        //     $table->float('costo_unitario', 8, 2);
        //     $table->integer('unidades')->default(0);
        //     $table->double('total', 16, 2)->default(0);
        //     $table->integer('unidades_pagado')->default(0);
        //     $table->double('total_pagado', 16, 2)->default(0);
        //     $table->integer('unidades_devuelto')->default(0);
        //     $table->double('total_devuelto', 16, 2)->default(0);
        //     $table->integer('unidades_pendiente')->default(0);
        //     $table->double('total_pendiente', 16, 2)->default(0);
        //     $table->integer('unidades_base')->default(0);
        //     $table->double('total_base', 16, 2)->default(0);
        //     $table->timestamps();
        // });

        // Schema::create('payments', function (Blueprint $table) {
        //     $table->bigIncrements('id');
        //     $table->unsignedBigInteger('register_id');
        //     $table->foreign('register_id')->references('id')->on('registers');
        //     $table->integer('unidades')->default(0);
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
        Schema::dropIfExists('notes');
        Schema::dropIfExists('registers');
        Schema::dropIfExists('payments');
    }
}
