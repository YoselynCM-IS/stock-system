<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;


class EditorialVendidosExport implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct($editorial, $fecha1, $fecha2)
    {
        $this->editorial = $editorial;
        $this->fecha1 = $fecha1;
        $this->fecha2 = $fecha2;
    }

    public function view(): View
    {
        $vendidos = $this->get_vendidos();
        return view('download.excel.vendidos.reporte-vendidos-gral', [
            'fecha' => Carbon::now(),
            'cliente' => null,
            'editorial' => $this->editorial,
            'inicio' => $this->fecha1,
            'final' => $this->fecha2,
            'datos' => $vendidos,
            'totales' => $this->totales($vendidos)
        ]);
    }

    public function get_vendidos(){
        if($this->fecha2 === '0000-00-00'){
            if($this->editorial === 'TODO'){
                $datos = $this->todo_sin_fecha();
            } else {
                $datos = \DB::table('vendidos')
                    ->join('libros', 'vendidos.libro_id', '=', 'libros.id')
                    ->where('libros.editorial', $this->editorial)
                    ->select(
                        'libros.id as libro_id',
                        'libros.titulo as libro',
                        \DB::raw('SUM(unidades) as unidades_vendido'),
                        \DB::raw('SUM(total) as total_vendido'),
                        \DB::raw('SUM(unidades_resta) as unidades_pendiente'),
                        \DB::raw('SUM(total_resta) as total_pendiente')
                    )
                    ->groupBy('libros.titulo', 'libros.id')
                    ->orderBy('libros.titulo', 'asc')
                    ->get();
            }
        } else {
            if($this->editorial === 'TODO'){
                $datos = $this->todo_con_fecha($this->fecha1, $this->fecha2);
            } else {
                $datos = \DB::table('vendidos')
                    ->join('libros', 'vendidos.libro_id', '=', 'libros.id')
                    ->where('libros.editorial', $this->editorial)
                    ->whereBetween('vendidos.created_at', [$this->fecha1, $this->fecha2])
                    ->where(function ($query) {
                        $query->where('vendidos.unidades', '>', 0)
                                ->orWhere('vendidos.unidades_resta', '>', 0);
                    })
                    ->select(
                        'libros.id as libro_id',
                        'libros.titulo as libro',
                        \DB::raw('SUM(unidades) as unidades_vendido'),
                        \DB::raw('SUM(total) as total_vendido'),
                        \DB::raw('SUM(unidades_resta) as unidades_pendiente'),
                        \DB::raw('SUM(total_resta) as total_pendiente')
                    )->groupBy('libros.titulo', 'libros.id')
                    ->get();
            }
        }
        return $datos;
    }

    public function todo_sin_fecha(){
        $datos = \DB::table('vendidos')
            ->join('libros', 'vendidos.libro_id', '=', 'libros.id')
            ->where('unidades', '>', 0)->orWhere('unidades_resta', '>', 0)
            ->select(
                'libros.id as libro_id',
                'libros.titulo as libro',
                \DB::raw('SUM(unidades) as unidades_vendido'),
                \DB::raw('SUM(total) as total_vendido'),
                \DB::raw('SUM(unidades_resta) as unidades_pendiente'),
                \DB::raw('SUM(total_resta) as total_pendiente')
            )
            ->groupBy('libros.titulo', 'libros.id')
            ->orderBy('libros.titulo', 'asc')
            ->get();
        return $datos;
    }

    public function todo_con_fecha($fecha1, $fecha2){
        $datos = \DB::table('vendidos')
            ->join('libros', 'vendidos.libro_id', '=', 'libros.id')
            ->whereBetween('vendidos.created_at', [$fecha1, $fecha2])
            ->where(function ($query) {
                $query->where('vendidos.unidades', '>', 0)
                        ->orWhere('vendidos.unidades_resta', '>', 0);
            })
            ->select(
                'libros.id as libro_id',
                'libros.titulo as libro',
                \DB::raw('SUM(unidades) as unidades_vendido'),
                \DB::raw('SUM(total) as total_vendido'),
                \DB::raw('SUM(unidades_resta) as unidades_pendiente'),
                \DB::raw('SUM(total_resta) as total_pendiente')
            )->groupBy('libros.titulo', 'libros.id')
            ->get();
        return $datos;
    }

    // OBTENER TOTALES
    public function totales($vendidos){
        $unidades_vendido = 0;
        $subtotal_vendidas = 0;
        $unidades_pendiente = 0;
        $subtotal_pendientes = 0;

        foreach($vendidos as $vendido){
            $unidades_vendido += $vendido->unidades_vendido;
            $subtotal_vendidas += $vendido->total_vendido;
            $unidades_pendiente += $vendido->unidades_pendiente;
            $subtotal_pendientes += $vendido->total_pendiente;
        }
        $totales = [
            'unidades_vendido' => $unidades_vendido,
            'subtotal_vendidas' => $subtotal_vendidas,
            'unidades_pendiente' => $unidades_pendiente,
            'subtotal_pendientes' => $subtotal_pendientes
        ];
        return $totales;
    }
}
