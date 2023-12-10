<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Enteditoriale;
use App\Remisione;
use App\Promotion;
use App\Entrada;
use App\Cliente;
use App\Libro;
use App\Regalo;
use App\Remcliente;
use App\Order;
use App\Note;

class OficinaController extends Controller
{
    public function remisiones(){
        return view('oficina.remisiones');
    }

    public function pagos(){
        return view('oficina.pagos');
    }

    public function pedidos(){
        return view('oficina.pedidos');
    }

    public function promociones(){
        return view('oficina.promociones');
    }

    public function donaciones(){
        return view('oficina.donaciones');
    }
    
    public function entradas(){
        $editoriales = \DB::table('editoriales')->orderBy('editorial', 'asc')->get();
        return view('oficina.entradas', compact('editoriales'));
    }

    public function libros(){
        $editoriales = \DB::table('editoriales')->orderBy('editorial', 'asc')->get();
        return view('oficina.libros', compact('editoriales'));
    }  
    
    public function clientes(){
        return view('oficina.clientes');
    }

    public function show_editoriales(){
        $editoriales = \DB::table('editoriales')->orderBy('editorial', 'asc')->get();
        return response()->json($editoriales);
    }

    // FECHA DE ADEUDOS DE REMISIONES
    public function fecha_adeudo(){
        return view('oficina.fecha-adeudo');
    }

    // CERRAR REMISIONES
    public function cerrar(){
        $responsables = \DB::table('responsables')->orderBy('responsable', 'asc')->get();
        return view('oficina.cerrar', compact('responsables'));
    }

    // PAGOS DE ENTRADAS
    public function entradas_pagos(){
        $editoriales = Enteditoriale::orderBy('editorial', 'asc')
                        ->withCount('entdepositos')->get();
        return view('oficina.entradas.pagos', compact('editoriales'));
    }

    public function notas(){
        return view('oficina.notas');
    }

    public function salidas(){
        return view('oficina.salidas');
    }

    public function entradas_salidas(){
        return view('oficina.entradas-salidas');
    }

    public function codes(){
        return view('oficina.codes');
    }
}
