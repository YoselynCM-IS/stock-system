<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDestinatariosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create('destinatarios', function (Blueprint $table) {
        //     $table->bigIncrements('id');
        //     $table->string('destinatario', 100)->unique();
        //     $table->string('rfc');
        //     $table->text('direccion');
        //     $table->enum('regimen_fiscal', ['pf con ae', 'pf sin ae', 'pm de lg', 'pm sin fl']);
        //     $table->string('telefono');
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
        Schema::dropIfExists('destinatarios');
    }
}
