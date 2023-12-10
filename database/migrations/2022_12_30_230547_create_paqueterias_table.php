<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaqueteriasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create('paqueterias', function (Blueprint $table) {
        //     $table->bigIncrements('id');
        //     $table->unsignedBigInteger('destinatario_id');
        //     $table->foreign('destinatario_id')->references('id')->on('destinatarios');
        //     $table->string('paqueteria');
        //     $table->dateTime('fecha_envio');
        //     $table->enum('tipo_envio', ['terrestre', 'aereo']);
        //     $table->double('precio');
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
        Schema::dropIfExists('paqueterias');
    }
}
