<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePromotionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create('promotions', function (Blueprint $table) {
        //     $table->bigIncrements('id');
        //     $table->string('folio', 50)->unique();
        //     $table->string('plantel');
        //     $table->text('descripcion')->nullable();
        //     $table->integer('unidades')->default(0);
        //     $table->integer('unidades_pendientes')->default(0);
        //     $table->text('entregado_por')->nullable();
        //     $table->timestamps();
        // });

        // Schema::create('departures', function (Blueprint $table) {
        //     $table->bigIncrements('id');
        //     $table->unsignedBigInteger('promotion_id')->nullable();
        //     $table->foreign('promotion_id')->references('id')->on('promotions');
        //     $table->unsignedBigInteger('libro_id')->nullable();
        //     $table->foreign('libro_id')->references('id')->on('libros');
        //     $table->integer('unidades')->default(0);
        //     $table->integer('unidades_pendientes')->default(0);
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
        Schema::dropIfExists('promotions');
        Schema::dropIfExists('departures');
    }
}
