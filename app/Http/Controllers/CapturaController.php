<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
USE App\Remisione;

class CapturaController extends Controller
{
    // MOSTRAR TODAS LAS REMISIONES
    public function remisiones(){
        return view('oficina.remisiones');
    }
}
