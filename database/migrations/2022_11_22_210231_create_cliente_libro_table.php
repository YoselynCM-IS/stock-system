<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClienteLibroTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create('cliente_libro', function (Blueprint $table) {
        //     $table->bigIncrements('id');
        //     $table->unsignedBigInteger('cliente_id');
        //     $table->foreign('cliente_id')->references('id')->on('clientes');
        //     $table->unsignedBigInteger('libro_id')->nullable();
        //     $table->foreign('libro_id')->references('id')->on('libros');
        //     $table->float('costo_unitario', 8, 2);
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
        Schema::dropIfExists('cliente_libro');
    }
}
