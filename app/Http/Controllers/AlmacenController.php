<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Promotion;
use App\Remisione;
use App\Entrada;
use App\Regalo;
use App\Libro;
use App\Note;
use App\Remcliente;
use App\Order;

class AlmacenController extends Controller
{
    public function remisiones(){;
        return view('almacen.remisiones');
    }

    public function devoluciones(){ 
        $responsables = \DB::table('responsables')->orderBy('responsable', 'asc')->get();
        return view('almacen.devoluciones', compact('responsables'));
    }

    public function notas(){
        return view('almacen.notas');
    }

    public function pedidos(){
        return view('almacen.pedidos');
    }

    public function promociones(){
        return view('almacen.promociones');
    }

    public function donaciones(){
        $regalos = Regalo::orderBy('id','desc')->get();
        return view('almacen.donaciones', compact('regalos'));
    }

    public function entradas(){
        $editoriales = \DB::table('editoriales')->orderBy('editorial', 'asc')->get();
        return view('almacen.entradas', compact('editoriales'));
    }
    
    public function libros(){
        $editoriales = \DB::table('editoriales')->orderBy('editorial', 'asc')->get();
        return view('almacen.libros', compact('editoriales'));
    }

    public function movimientos(){
        $editoriales = \DB::table('editoriales')->orderBy('editorial', 'asc')->get();
        return view('almacen.movimientos', compact('editoriales'));
    }

    public function entradas_salidas(){
        return view('almacen.entradas-salidas');
    }

    public function codes(){
        return view('almacen.codes');
    }
}
