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
        Schema::create('claves', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('libro_id');
            $table->foreign('libro_id')->references('id')->on('libros');
            $table->enum('tipo', ['profesor','demo']);
            $table->integer('piezas')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('claves');
    }
};
