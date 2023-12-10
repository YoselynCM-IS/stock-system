<?php

namespace App\Exports;

use App\Note;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class NotesExport implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct($cliente, $inicio, $final, $tipo)
    {
        $this->cliente = $cliente;
        $this->inicio = $inicio;
        $this->final = $final;
        $this->tipo = $tipo;
    }

    public function view(): View
    {
        if($this->tipo === 'general'){
            $notes = $this->get_general();
        }
        if($this->tipo === 'detallado'){
            $notes = $this->get_detallado();
        }
        $totales = $this->acumular_totales($notes);
        return view('download.excel.notes.reporte-notes', [
            'fecha' => Carbon::now(),
            'inicio' => $this->inicio,
            'final' => $this->final,
            'notes' => $notes,
            'totales' => $totales,
            'tipo' => $this->tipo
        ]);
    }

    public function get_detallado(){
        if($this->cliente === 'null' && $this->final === '0000-00-00'){
            $notes = Note::with('registers.libro')->orderBy('folio','desc')->get();
        }
        if($this->cliente !== 'null' && $this->inicio === '0000-00-00' && $this->final === '0000-00-00'){
            $notes = Note::with('registers.libro')
                    ->where('cliente','like','%'.$this->cliente.'%')->orderBy('folio','desc')->get();
        }
        if($this->inicio !== '0000-00-00' && $this->final !== '0000-00-00'){
            $fechas = $this->format_date($this->inicio, $this->final);
            $fecha1 = $fechas['inicio'];
            $fecha2 = $fechas['final'];

            if($this->cliente === 'null'){
                $notes = Note::with('registers.libro')
                    ->whereBetween('created_at', [$fecha1, $fecha2])
                    ->orderBy('id','desc')->get(); 
            }
            if($this->cliente !== 'null'){
                $notes = Note::with('registers.libro')
                    ->where('cliente','like','%'.$this->cliente.'%')
                    ->whereBetween('created_at', [$fecha1, $fecha2])
                    ->orderBy('id','desc')->get(); 
            }
        }
        return $notes;
    }

    public function get_general(){
        if($this->cliente === 'null' && $this->final === '0000-00-00'){
            $notes = Note::orderBy('folio','desc')->get();
        }
        if($this->cliente !== 'null' && $this->inicio === '0000-00-00' && $this->final === '0000-00-00'){
            $notes = Note::where('cliente','like','%'.$this->cliente.'%')->orderBy('folio','desc')->get();
        }
        if($this->inicio !== '0000-00-00' && $this->final !== '0000-00-00'){
            $fechas = $this->format_date($this->inicio, $this->final);
            $fecha1 = $fechas['inicio'];
            $fecha2 = $fechas['final'];

            if($this->cliente === 'null'){
                $notes = Note::whereBetween('created_at', [$fecha1, $fecha2])
                    ->orderBy('id','desc')->get(); 
            }
            if($this->cliente !== 'null'){
                $notes = Note::where('cliente','like','%'.$this->cliente.'%')
                    ->whereBetween('created_at', [$fecha1, $fecha2])
                    ->orderBy('id','desc')->get(); 
            }
        }
        return $notes;
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

    public function acumular_totales($notes){
        $total_salida = 0;
        $total_pagos = 0;
        $total_devolucion = 0;
        $total_pagar = 0;
        foreach($notes as $note){
            $total_salida += $note->total_salida;
            $total_pagos += $note->pagos;
            $total_devolucion += $note->total_devolucion;
            $total_pagar += $note->total_pagar;
        }
        $totales = [
            'total_salida' => $total_salida,
            'total_pagos' => $total_pagos,
            'total_devolucion' => $total_devolucion,
            'total_pagar' => $total_pagar
        ];
        return $totales;
    }
}
