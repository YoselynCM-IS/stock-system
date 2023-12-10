<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class VendidosDetallesExport implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct($fecha1, $fecha2)
    {
        $this->fecha1 = $fecha1;
        $this->fecha2 = $fecha2;
    }

    public function view(): View
    {
        return view('download.excel.vendidos.reporte-vendidos-detallado', [
            'fecha' => Carbon::now(),
            'inicio' => $this->fecha1,
            'final' => $this->fecha2,
            'vendidos' => $this->get_vendidos()
        ]);
    }

    public function get_vendidos() {
        $vendidos = array();
        if($this->fecha2 === '0000-00-00'){
            $clientes = \DB::table('vendidos')
                ->join('remisiones', 'vendidos.remisione_id', '=', 'remisiones.id')
                ->join('clientes', 'remisiones.cliente_id', '=', 'clientes.id')
                ->select(
                    'clientes.id as id',
                    'clientes.name as name', 
                    \DB::raw('SUM(vendidos.unidades) as unidades_vendidas'),
                    \DB::raw('SUM(vendidos.unidades_resta) as unidades_pendientes')
                )
                ->groupBy('clientes.name')
                ->get();
            
            foreach($clientes as $cliente){
                $registros = \DB::table('vendidos')
                    ->join('remisiones', 'vendidos.remisione_id', '=', 'remisiones.id')
                    ->join('clientes', 'remisiones.cliente_id', '=', 'clientes.id')
                    ->join('libros', 'vendidos.libro_id', '=', 'libros.id')
                    ->where('clientes.id', $cliente->id)
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

                $dato = [
                    'cliente' => $cliente->name,
                    'registros' => $registros
                ];

                array_push($vendidos, $dato);
            }
        } else {
            $clientes = \DB::table('vendidos')
                ->join('remisiones', 'vendidos.remisione_id', '=', 'remisiones.id')
                ->join('clientes', 'remisiones.cliente_id', '=', 'clientes.id')
                ->whereBetween('vendidos.created_at', [$this->fecha1, $this->fecha2])
                ->select(
                    'clientes.id as id',
                    'clientes.name as name', 
                    \DB::raw('SUM(vendidos.unidades) as unidades_vendidas'),
                    \DB::raw('SUM(vendidos.unidades_resta) as unidades_pendientes')
                )
                ->groupBy('clientes.name')
                ->get();
            
            foreach($clientes as $cliente){
                $registros = \DB::table('vendidos')
                    ->join('remisiones', 'vendidos.remisione_id', '=', 'remisiones.id')
                    ->join('clientes', 'remisiones.cliente_id', '=', 'clientes.id')
                    ->join('libros', 'vendidos.libro_id', '=', 'libros.id')
                    ->where('clientes.id', $cliente->id)
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

                $dato = [
                    'cliente' => $cliente->name,
                    'registros' => $registros
                ];

                array_push($vendidos, $dato);
            }
        }
        return $vendidos;
    }
}
