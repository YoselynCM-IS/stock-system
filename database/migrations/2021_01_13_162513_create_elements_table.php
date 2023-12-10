<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateElementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create('elements', function (Blueprint $table) {
        //     $table->bigIncrements('id');
        //     $table->unsignedBigInteger('order_id');
        //     $table->unsignedBigInteger('libro_id');
        //     $table->integer('quantity')->default(0);
        //     $table->float('unit_price', 8, 2);
        //     $table->double('total', 16, 2)->default(0);
        //     $table->integer('actual_quantity')->default(0);
        //     $table->double('actual_total', 16, 2)->default(0);
        //     $table->foreign('order_id')->references('id')->on('orders');
        //     $table->foreign('libro_id')->references('id')->on('libros');
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
        Schema::dropIfExists('elements');
    }
}
