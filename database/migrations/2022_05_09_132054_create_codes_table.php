<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create('codes', function (Blueprint $table) {
        //     $table->bigIncrements('id');
        //     $table->unsignedBigInteger('libro_id')->nullable();
        //     $table->foreign('libro_id')->references('id')->on('libros');
        //     $table->string('codigo')->unique();
        //     $table->enum('tipo', ['profesor','alumno'])->default('alumno');
        //     $table->enum('estado', ['proceso','inventario', 'ocupado', 'eliminado'])->default('proceso');
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
        Schema::dropIfExists('codes');
    }
}
