<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRemisionesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create('remisiones', function (Blueprint $table) {
        //     $table->bigIncrements('id');
        //     $table->unsignedBigInteger('user_id');
        //     $table->foreign('user_id')->references('id')->on('users');
        //     $table->unsignedBigInteger('cliente_id');
        //     $table->foreign('cliente_id')->references('id')->on('clientes');
        //     $table->double('total', 16, 2)->default(0);
        //     $table->double('total_devolucion', 16, 2)->default(0);
        //     $table->double('total_pagar', 16, 2)->default(0);
        //     $table->double('pagos', 16, 2)->default(0);
        //     $table->date('fecha_entrega');
        //     $table->enum('estado', ['Iniciado', 'Proceso', 'Terminado', 'Cancelado']);
        //     $table->date('fecha_creacion');
        //     $table->date('fecha_devolucion');
        //     $table->text('responsable')->nullable();
        //     $table->timestamps();
        // }); 

        // Schema::create('datos', function (Blueprint $table) {
        //     $table->bigIncrements('id');
        //     $table->unsignedBigInteger('remisione_id')->nullable();
        //     $table->foreign('remisione_id')->references('id')->on('remisiones');
        //     $table->unsignedBigInteger('libro_id')->nullable();
        //     $table->foreign('libro_id')->references('id')->on('libros');
        //     $table->float('costo_unitario', 8, 2);
        //     $table->integer('unidades')->default(0);
        //     $table->double('total', 16, 2)->default(0);
        //     $table->softDeletes(); //Nueva línea, para el borrado lógico
        //     $table->timestamps();
        // });

        // Schema::create('devoluciones', function (Blueprint $table) {
        //     $table->bigIncrements('id');
        //     $table->unsignedBigInteger('remisione_id')->nullable();
        //     $table->foreign('remisione_id')->references('id')->on('remisiones');
        //     $table->unsignedBigInteger('dato_id')->nullable();
        //     $table->foreign('dato_id')->references('id')->on('datos');
        //     $table->unsignedBigInteger('libro_id')->nullable();
        //     $table->foreign('libro_id')->references('id')->on('libros');
        //     $table->integer('unidades')->default(0);
        //     $table->integer('unidades_resta')->default(0);
        //     $table->integer('unidades_base')->default(0);
        //     $table->double('total_base', 16, 2)->default(0);
        //     $table->double('total', 16, 2)->default(0);
        //     $table->double('total_resta', 16, 2)->default(0);
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
        Schema::dropIfExists('remisiones');
        Schema::dropIfExists('datos');
        Schema::dropIfExists('devoluciones');
    }
}
