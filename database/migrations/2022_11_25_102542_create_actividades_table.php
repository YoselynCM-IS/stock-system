<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActividadesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create('actividades', function (Blueprint $table) {
        //     $table->bigIncrements('id');
        //     $table->unsignedBigInteger('user_id');
        //     $table->foreign('user_id')->references('id')->on('users');
        //     $table->string('nombre');
        //     $table->enum('tipo', ['llamar', 'enviarcorreo', 'reunion', 'videoconferencia', 'nota']);
        //     $table->text('lugar')->nullable();
        //     $table->dateTime('fecha');
        //     $table->text('descripcion');
        //     $table->enum('estado', ['pendiente', 'proximo', 'vencido', 'completado', 'cancelado']);
        //     $table->boolean('exitosa')->default(false);
        //     $table->text('observaciones')->nullable();
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
        Schema::dropIfExists('actividades');
    }
}
