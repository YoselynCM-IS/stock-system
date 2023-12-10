<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRegalosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create('regalos', function (Blueprint $table) {
        //     $table->bigIncrements('id');
        //     $table->string('plantel');
        //     $table->text('descripcion')->nullable();
        //     $table->integer('unidades')->default(0);
        //     $table->text('entregado_por')->nullable();
        //     $table->timestamps();
        // });

        // Schema::create('donaciones', function (Blueprint $table) {
        //     $table->bigIncrements('id');
        //     $table->unsignedBigInteger('regalo_id')->nullable();
        //     $table->foreign('regalo_id')->references('id')->on('regalos');
        //     $table->unsignedBigInteger('libro_id')->nullable();
        //     $table->foreign('libro_id')->references('id')->on('libros');
        //     $table->integer('unidades')->default(0);
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
        Schema::dropIfExists('regalos');
        Schema::dropIfExists('donaciones');
    }
}
