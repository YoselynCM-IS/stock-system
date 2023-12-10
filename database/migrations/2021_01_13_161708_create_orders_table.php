<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create('orders', function (Blueprint $table) {
        //     $table->bigIncrements('id');
        //     $table->string('identifier');
        //     $table->date('date');
        //     $table->string('provider');
        //     $table->string('destination');
        //     $table->double('total_bill', 16, 2)->default(0);
        //     $table->enum('status', ['espera', 'cancelado', 'rechazado', 'completo', 'incompleto'])->default('espera');
        //     $table->text('observations')->nullable();
        //     $table->double('actual_total_bill', 16, 2)->default(0);
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
        Schema::dropIfExists('orders');
    }
}
