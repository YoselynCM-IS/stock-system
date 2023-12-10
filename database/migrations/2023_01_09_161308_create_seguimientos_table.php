<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSeguimientosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create('seguimientos', function (Blueprint $table) {
        //     $table->bigIncrements('id');
        //     $table->unsignedBigInteger('user_id');
        //     $table->foreign('user_id')->references('id')->on('users');
        //     $table->unsignedBigInteger('cliente_id');
        //     $table->foreign('cliente_id')->references('id')->on('clientes');
        //     $table->enum('tipo', ['llamada', 'correo', 'mensaje', 'visita', 'nota']);
        //     $table->string('situacion')->nullable();
        //     $table->string('respuesta')->nullable();
        //     $table->dateTime('fecha_hora');
        //     $table->string('duracion')->nullable();
        //     $table->text('comentario');
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
        Schema::dropIfExists('seguimientos');
    }
}
