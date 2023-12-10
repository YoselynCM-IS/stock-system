<?php

namespace App\Exports;

use App\Cliente;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ClienteVendidosExport implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct($cliente_id, $fecha1, $fecha2)
    {
        $this->cliente_id = $cliente_id;
        $this->fecha1 = $fecha1;
        $this->fecha2 = $fecha2;
    }

    public function view(): View
    {
        $cliente = Cliente::findOrFail($this->cliente_id);
        $vendidos = $this->get_vendidos();
        return view('download.excel.vendidos.reporte-vendidos-gral', [
            'fecha' => Carbon::now(),
            'cliente' => $cliente,
            'editorial' => null,
            'inicio' => $this->fecha1,
            'final' => $this->fecha2,
            'datos' => $vendidos,
            'totales' => $this->totales($vendidos)
        ]);
    }

    public function get_vendidos(){
        if($this->fecha2 === '0000-00-00'){
            $datos = \DB::table('vendidos')
                ->join('remisiones', 'vendidos.remisione_id', '=', 'remisiones.id')
                ->join('clientes', 'remisiones.cliente_id', '=', 'clientes.id')
                ->join('libros', 'vendidos.libro_id', '=', 'libros.id')
                ->where('clientes.id', $this->cliente_id)
                ->where(function ($query) {
                    $query->where('vendidos.unidades', '>', 0)
                            ->orWhere('vendidos.unidades_resta', '>', 0);
                })
                ->select(
                    'libros.id as libro_id',
                    'libros.titulo as libro',
                    \DB::raw('SUM(vendidos.unidades) as unidades_vendido'),
                    \DB::raw('SUM(vendidos.total) as total_vendido'),
                    \DB::raw('SUM(vendidos.unidades_resta) as unidades_pendiente'),
                    \DB::raw('SUM(vendidos.total_resta) as total_pendiente')
                )
                ->groupBy('libros.titulo', 'libros.id')
                ->orderBy('libros.titulo', 'asc')
                ->get();
        } else {
            $datos = \DB::table('vendidos')
                ->join('remisiones', 'vendidos.remisione_id', '=', 'remisiones.id')
                ->join('clientes', 'remisiones.cliente_id', '=', 'clientes.id')
                ->join('libros', 'vendidos.libro_id', '=', 'libros.id')
                ->where('clientes.id', $this->cliente_id)
                ->whereBetween('vendidos.created_at', [$this->fecha1, $this->fecha2])
                ->where(function ($query) {
                    $query->where('vendidos.unidades', '>', 0)
                            ->orWhere('vendidos.unidades_resta', '>', 0);
                })
                ->select(
                    'libros.id as libro_id',
                    'libros.titulo as libro',
                    \DB::raw('SUM(vendidos.unidades) as unidades_vendido'),
                    \DB::raw('SUM(vendidos.total) as total_vendido'),
                    \DB::raw('SUM(vendidos.unidades_resta) as unidades_pendiente'),
                    \DB::raw('SUM(vendidos.total_resta) as total_pendiente')
                )
                ->groupBy('libros.titulo', 'libros.id')
                ->orderBy('libros.titulo', 'asc')
                ->get();
        }
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
