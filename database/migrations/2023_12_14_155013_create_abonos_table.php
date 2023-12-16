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
        Schema::create('abonos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('adeudo_id');
            $table->foreign('adeudo_id')->references('id')->on('adeudos');
            $table->date('fecha');
            $table->double('pago', 16, 2)->default(0);
            $table->text('nota');
            $table->string('ingresado_por');
            $table->softDeletes($column = 'deleted_at', $precision = 0);
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
        Schema::dropIfExists('abonos');
    }
};
