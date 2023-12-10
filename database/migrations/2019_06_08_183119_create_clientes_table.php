<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClientesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create('clientes', function (Blueprint $table) {
        //     $table->bigIncrements('id');
        //     $table->string('name', 100)->unique();
        //     $table->string('contacto')->nullable();
        //     $table->string('email');
        //     $table->string('telefono');
        //     $table->text('direccion');
        //     $table->string('condiciones_pago');
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
        Schema::dropIfExists('clientes');
    }
}
