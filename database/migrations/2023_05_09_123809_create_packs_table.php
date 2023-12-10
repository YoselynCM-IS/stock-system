<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create('packs', function (Blueprint $table) {
        //     $table->bigIncrements('id');
        //     $table->integer('libro_fisico');
        //     $table->integer('libro_digital');
        //     $table->integer('piezas')->default(0);
        //     $table->integer('defectuosos')->default(0);
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
        Schema::dropIfExists('packs');
    }
}
