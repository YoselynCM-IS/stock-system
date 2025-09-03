<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create('surtidos', function (Blueprint $table) {
        //     $table->id();
        //     $table->unsignedBigInteger('pedido_id')->nullable();
        //     $table->foreign('pedido_id')->references('id')->on('pedidos');
        //     $table->string('relacion_tabla');
        //     $table->integer('relacion_id');
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
        Schema::dropIfExists('surtidos');
    }
};
