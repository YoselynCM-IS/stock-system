<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePedidosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create('pedidos', function (Blueprint $table) {
        //     $table->bigIncrements('id');
        //     $table->unsignedBigInteger('user_id');
        //     $table->foreign('user_id')->references('id')->on('users');
        //     $table->unsignedBigInteger('cliente_id');
        //     $table->foreign('cliente_id')->references('id')->on('clientes');
        //     $table->integer('total_quantity')->default(0);
        //     $table->integer('total_solicitar')->default(0);
        //     $table->enum('estado', ['proceso', 'cancelado', 'en orden', 'de inventario'])->default('proceso');
        //     $table->text('comentarios')->nullable();
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
        Schema::dropIfExists('pedidos');
    }
}
