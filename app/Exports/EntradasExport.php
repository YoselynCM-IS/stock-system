<?php

namespace App\Exports;

use App\Entrada;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class EntradasExport implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct($inicio, $final, $editorial, $tipo)
    {
        $this->editorial = $editorial;
        $this->inicio = $inicio;
        $this->final = $final;
        $this->tipo = $tipo;
    }

    public function view(): View
    {
        if($this->tipo === 'general'){
            $datos = $this->get_general($this->editorial, $this->inicio, $this->final);
        }
        if($this->tipo === 'detallado'){
            $datos = $this->get_detallado($this->editorial, $this->inicio, $this->final);
        }
        $totales = $this->acumular_totales($datos['entradas']);
       
        return view('download.excel.entradas.reporte-entradas', [
            'fecha' => Carbon::now(),
            'inicio' => $this->inicio,
            'final' => $this->final,
            'editorial' => $this->editorial,
            'totales' => $totales,
            'entradas' => $datos['entradas'],
            // 'grupos' => $datos['grupos'],
            'tipo' => $this->tipo
        ]);
    }

    // OBTENER TODAS LAS ENTRADAS - GENERAL
    public function get_general($editorial, $inicio, $final){
        $grupos = null;
        if($final != '0000-00-00'){
            $fechas = $this->format_date($inicio, $final);
            $inicio = $fechas['inicio'];
            $final = $fechas['final'];
        }

        if($final === '0000-00-00' && $editorial === 'TODAS'){
            $entradas = Entrada::orderBy('editorial','asc')->get();
            // $grupos = \DB::table('entradas')
            //         ->select(
            //             'editorial as editorial',
            //             \DB::raw('SUM(unidades) as unidades'),
            //             \DB::raw('SUM(total) as total'),
            //             \DB::raw('SUM(total_pagos) as total_pagos'),
            //             \DB::raw('SUM(total) - SUM(total_pagos) as total_pendiente')
            //         )
            //         ->groupBy('editorial')
            //         ->orderBy('editorial','asc')
            //         ->get();
        }
            
        if($final !== '0000-00-00' && $editorial === 'TODAS'){
            $entradas = Entrada::whereBetween('created_at', [$inicio, $final])->orderBy('editorial','asc')->get();
            // $grupos = \DB::table('entradas')
            //         ->whereBetween('created_at', [$inicio, $final])
            //         ->select(
            //             'editorial as editorial',
            //             \DB::raw('SUM(unidades) as unidades'),
            //             \DB::raw('SUM(total) as total'),
            //             \DB::raw('SUM(total_pagos) as total_pagos'),
            //             \DB::raw('SUM(total) - SUM(total_pagos) as total_pendiente')
            //         )
            //         ->groupBy('editorial')
            //         ->orderBy('editorial','asc')
            //         ->get();
        } 
        if($final === '0000-00-00' && $editorial !== 'TODAS')
            $entradas = Entrada::where('editorial', $editorial)->orderBy('id','desc')->get();
        if($final !== '0000-00-00' && $editorial !== 'TODAS'){
            $entradas = Entrada::where('editorial', $editorial)
                        ->whereBetween('created_at', [$inicio, $final])
                        ->orderBy('id','desc')->get();
        }
        $datos = [
            'entradas' => $entradas,
            'grupos' => $grupos
        ];
        return $datos;
    }

    // OBTENER TODAS LAS ENTRADAS - DETALLADO
    public function get_detallado($editorial, $inicio, $final){
        $grupos = null;
        if($final != '0000-00-00'){
            $fechas = $this->format_date($inicio, $final);
            $inicio = $fechas['inicio'];
            $final = $fechas['final'];
        }

        if($final === '0000-00-00' && $editorial === 'TODAS'){
            $entradas = Entrada::orderBy('editorial','asc')->with('registros.libro')->get();
        }
        if($final !== '0000-00-00' && $editorial === 'TODAS'){
            $entradas = Entrada::whereBetween('created_at', [$inicio, $final])
                        ->with('registros.libro')
                        ->orderBy('editorial','asc')->get();
        } 
        if($final === '0000-00-00' && $editorial !== 'TODAS')
            $entradas = Entrada::where('editorial', $editorial)->with('registros.libro')->orderBy('id','desc')->get();
        if($final !== '0000-00-00' && $editorial !== 'TODAS'){
            $entradas = Entrada::where('editorial', $editorial)
                        ->with('registros.libro')
                        ->whereBetween('created_at', [$inicio, $final])
                        ->orderBy('id','desc')->get();
        }
        $datos = [
            'entradas' => $entradas,
            'grupos' => $grupos
        ];
        return $datos;
    }

    public function format_date($fecha1, $fecha2){
        $inicio = new Carbon($fecha1);
        $final 	= new Carbon($fecha2);
        $inicio = $inicio->format('Y-m-d 00:00:00');
        $final 	= $final->format('Y-m-d 23:59:59');

        $fechas = [
            'inicio' => $inicio,
            'final' => $final
        ];

        return $fechas;
    }

    public function acumular_totales($entradas){
        $total_unidades = 0;
        $total = 0;
        $total_pagos = 0;
        $total_pendiente = 0;
        $total_devolucion = 0;
        foreach($entradas as $entrada){
            $total_unidades += $entrada->unidades;     
            $total += $entrada->total;
            $total_pagos += $entrada->total_pagos;
            $total_devolucion += $entrada->total_devolucion;
            $total_pendiente += $entrada->total - $entrada->total_pagos;

        }
        $totales = [
            'total_unidades' => $total_unidades,
            'total' => $total,
            'total_pagos' => $total_pagos,
            'total_pendiente' => $total_pendiente,
            'total_devolucion' => $total_devolucion
        ];
        return $totales;
    }
}
