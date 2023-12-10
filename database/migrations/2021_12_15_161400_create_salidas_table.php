<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalidasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create('salidas', function (Blueprint $table) {
        //     $table->bigIncrements('id');
        //     $table->string('creado_por');
        //     $table->string('folio', 50)->unique();
        //     $table->integer('unidades')->default(0);
        //     $table->enum('estado', ['proceso', 'enviado'])->default('proceso');
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
        Schema::dropIfExists('salidas');
    }
}
