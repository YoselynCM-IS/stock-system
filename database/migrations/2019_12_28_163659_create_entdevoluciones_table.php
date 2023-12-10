<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEntdevolucionesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create('entdevoluciones', function (Blueprint $table) {
        //     $table->bigIncrements('id');
        //     $table->unsignedBigInteger('entrada_id')->nullable();
        //     $table->foreign('entrada_id')->references('id')->on('entradas');
        //     $table->unsignedBigInteger('registro_id')->nullable();
        //     $table->foreign('registro_id')->references('id')->on('registros');
        //     $table->integer('unidades')->default(0);
        //     $table->double('total', 16, 2)->default(0);
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
        Schema::dropIfExists('entdevoluciones');
    }
}
