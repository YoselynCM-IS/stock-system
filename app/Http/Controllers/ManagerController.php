<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Enteditoriale;
use App\Promotion;
use App\Entrada;
use App\Regalo;
use App\Order;
use App\Note;

class ManagerController extends Controller
{
    // INICIO CORTES
    public function lista_cortes(){
        return view('information.cortes.clientes.lista');
    }

    // PAGOS DE CLIENTES
    public function cortes_pagos(){
        return view('manager.cortes.pagos');
    }

    // MOVIMEINTOS DE LAS CUENTAS DE LOS CLIENTES
    public function movimientos_clientes(){
        return view('manager.movimientos.movimientos-clientes');
    }

    // MOVIMIENTOS DE LOS LIBROS
    public function movimientos_libros(){
        $editoriales = $this->get_editoriales();
        return view('manager.movimientos.movimientos-libros', compact('editoriales'));
    }

    // LISTA DE REMISIONES
    public function lista_remisiones(){
        $responsables = $this->get_responsables();
        return view('manager.remisiones.lista', compact('responsables'));
    }

    // PAGO POR REMISION
    public function pago_devolucion(){
        $responsables = $this->get_responsables();
        return view('manager.remisiones.pago-devolucion', compact('responsables'));
    }

    // ADEUDOS
    public function fecha_adeudo(){
        return view('manager.remisiones.fecha-adeudo');
    }

    // NOTAS
    public function notas(){
        return view('manager.otros.notas');
    }

    public function promociones(){
        return view('manager.otros.promociones');
    }

    public function donaciones(){
        return view('manager.otros.donaciones');
    }

    public function lista_entradas(){
        $editoriales = $this->get_editoriales();
        return view('manager.entradas.lista', compact('editoriales'));
    }

    public function entradas_pagos(){
        $editoriales = Enteditoriale::orderBy('editorial', 'asc')
                        ->withCount('entdepositos')->get();
        return view('manager.entradas.pagos', compact('editoriales'));
    }

    public function libros(){
        $editoriales = $this->get_editoriales();
        return view('manager.libros', compact('editoriales'));
    }

    public function clientes(){
        return view('manager.clientes');
    }

    public function get_responsables(){
        return \DB::table('responsables')->orderBy('responsable', 'asc')->get();
    }

    public function get_entradas(){
        return Entrada::with('registros')->orderBy('id','desc')->get();
    }

    public function get_editoriales(){
        return \DB::table('editoriales')->orderBy('editorial', 'asc')->get();
    }

    public function entradas_salidas(){
        return view('manager.movimientos.entradas-salidas');
    }

    public function salidas(){
        return view('manager.salidas');
    }

    public function codes(){
        return view('manager.codes');
    }

    public function lista_users(){
        return view('manager.users.users');
    }
}
