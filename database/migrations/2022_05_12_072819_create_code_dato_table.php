<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCodeDatoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create('code_dato', function (Blueprint $table) {
        //     $table->bigIncrements('id');
        //     $table->unsignedBigInteger('code_id')->nullable();
        //     $table->foreign('code_id')->references('id')->on('codes');
        //     $table->unsignedBigInteger('dato_id')->nullable();
        //     $table->foreign('dato_id')->references('id')->on('datos');
        //     $table->boolean('devolucion')->default(false);
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
        Schema::dropIfExists('code_dato');
    }
}
