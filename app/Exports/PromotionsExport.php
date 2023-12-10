<?php

namespace App\Exports;

use App\Promotion;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class PromotionsExport implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct($plantel, $inicio, $final, $tipo)
    {
        $this->plantel = $plantel;
        $this->inicio = $inicio;
        $this->final = $final;
        $this->tipo = $tipo;
    }

    public function view(): View
    {
        if($this->tipo === 'general'){
            $promotions = $this->get_general();
        }
        if($this->tipo === 'detallado'){
            $promotions = $this->get_detallado();
        }
        $total_unidades = $this->acumular_totales($promotions);
        return view('download.excel.promotions.reporte-promociones', [
            'fecha' => Carbon::now(),
            'inicio' => $this->inicio,
            'final' => $this->final,
            'promotions' => $promotions,
            'total_unidades' => $total_unidades,
            'tipo' => $this->tipo
        ]);
    }

    public function get_general(){
        if($this->plantel === 'null' && $this->final === '0000-00-00'){
            $promotions = Promotion::orderBy('folio','desc')->get();
        }
        if($this->plantel !== 'null' && $this->inicio === '0000-00-00' && $this->final === '0000-00-00'){
            $promotions = Promotion::where('plantel','like','%'.$this->plantel.'%')->orderBy('folio','desc')->get();
        }
        if($this->inicio !== '0000-00-00' && $this->final !== '0000-00-00'){
            $fechas = $this->format_date($this->inicio, $this->final);
            $fecha1 = $fechas['inicio'];
            $fecha2 = $fechas['final'];

            if($this->plantel === 'null'){
                $promotions = Promotion::whereBetween('created_at', [$fecha1, $fecha2])
                    ->orderBy('id','desc')->get(); 
            }
            if($this->plantel !== 'null'){
                $promotions = Promotion::where('plantel','like','%'.$this->plantel.'%')
                    ->whereBetween('created_at', [$fecha1, $fecha2])
                    ->orderBy('id','desc')->get(); 
            }
        }
        return $promotions;
    }

    public function get_detallado(){
        if($this->plantel === 'null' && $this->final === '0000-00-00'){
            $promotions = Promotion::with('departures.libro')->orderBy('folio','desc')->get();
        }
        if($this->plantel !== 'null' && $this->inicio === '0000-00-00' && $this->final === '0000-00-00'){
            $promotions = Promotion::with('departures.libro')
                    ->where('plantel','like','%'.$this->plantel.'%')->orderBy('folio','desc')->get();
        }
        if($this->inicio !== '0000-00-00' && $this->final !== '0000-00-00'){
            $fechas = $this->format_date($this->inicio, $this->final);
            $fecha1 = $fechas['inicio'];
            $fecha2 = $fechas['final'];

            if($this->plantel === 'null'){
                $promotions = Promotion::with('departures.libro')
                    ->whereBetween('created_at', [$fecha1, $fecha2])
                    ->orderBy('id','desc')->get(); 
            }
            if($this->plantel !== 'null'){
                $promotions = Promotion::with('departures.libro')
                    ->where('plantel','like','%'.$this->plantel.'%')
                    ->whereBetween('created_at', [$fecha1, $fecha2])
                    ->orderBy('id','desc')->get(); 
            }
        }
        return $promotions;
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

    public function acumular_totales($promotions){
        $total_unidades = 0;
        foreach($promotions as $promotion){
            $total_unidades += $promotion->unidades;
        }
        return $total_unidades;
    }
}
