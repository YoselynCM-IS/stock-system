<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLibrosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create('libros', function (Blueprint $table) {
        //     $table->bigIncrements('id');
        //     $table->string('ISBN')->nullable();
        //     $table->string('titulo');
        //     $table->string('autor')->nullable();
        //     $table->string('editorial');
        //     $table->string('edicion')->nullable();
        //     $table->integer('piezas')->default(0);
        //     $table->integer('defectuosos')->default(0);
        //     $table->timestamps();
        // }); 

        // Schema::create('defectuosos', function (Blueprint $table) {
        //     $table->bigIncrements('id');
        //     $table->unsignedBigInteger('libro_id')->nullable();
        //     $table->foreign('libro_id')->references('id')->on('libros');
        //     $table->integer('numero')->default(0);
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
        Schema::dropIfExists('libros');
    }
}
