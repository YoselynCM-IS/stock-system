<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class MovFechasExport implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct($inicio, $final, $categoria)
    {
        $this->inicio = $inicio;
        $this->final = $final;
        $this->categoria = $categoria;
    }

    public function view(): View
    {
        $movimientos = $this->get_movimientos($this->categoria);
        return view('download.excel.libros.movimientos_fechas', [
            'movimientos' => $movimientos,
            'fecha' => Carbon::now(),
            'inicio' => $this->inicio,
            'final' => $this->final,
        ]);
    }

    public function get_movimientos($categoria){
        $fechas = $this->format_date($this->inicio, $this->final);
        $fecha1 = $fechas['inicio'];
        $fecha2 = $fechas['final'];

        // ENTRADAS
        if($categoria === 'ENTRADAS'){
            $datos = \DB::table('registros')
                ->join('libros', 'registros.libro_id', '=', 'libros.id')
                ->whereBetween('registros.created_at', [$fecha1, $fecha2])
                ->select(
                    // 'libro_id as libro_id',
                    'libros.titulo as libro',
                    \DB::raw('SUM(unidades) as unidades'),
                    \DB::raw('SUM(total) as total')
                )->groupBy('libro_id', 'libros.titulo')
                ->orderBy('libros.titulo', 'asc')
                ->get();
        }
        if($categoria === 'DEVOLUCIONES'){
            $datos = \DB::table('devoluciones')
                ->join('libros', 'devoluciones.libro_id', '=', 'libros.id')
                ->whereBetween('devoluciones.created_at', [$fecha1, $fecha2])
                ->whereNotIn('devoluciones.unidades', [0])
                ->select(
                    'libros.titulo as libro',
                    \DB::raw('SUM(unidades) as unidades'),
                    \DB::raw('SUM(total) as total')
                )
                ->orderBy('libros.titulo', 'asc')
                ->groupBy('libro_id', 'libros.titulo')
                ->get();
        }
        if($categoria === 'NOTASDEV'){
            $datos = \DB::table('registers')
                    ->join('libros', 'registers.libro_id', '=', 'libros.id')
                    ->whereNotIn('registers.unidades_devuelto', [0])
                    ->select(
                        'libros.titulo as libro',
                        \DB::raw('SUM(unidades_devuelto) as unidades'),
                        \DB::raw('SUM(total_devuelto) as total')
                    )->whereBetween('registers.created_at', [$fecha1, $fecha2])
                    ->orderBy('libros.titulo', 'asc')
                    ->groupBy('libro_id', 'libros.titulo')
                    ->get();
        }
        // SALIDAS
        if($categoria === 'REMISIONES'){
            $datos = \DB::table('datos')
                    ->join('remisiones', 'datos.remisione_id', '=', 'remisiones.id')
                    ->join('libros', 'datos.libro_id', '=', 'libros.id')
                    ->whereNotIn('remisiones.estado', ['Cancelado'])
                    ->whereBetween('datos.created_at', [$fecha1, $fecha2])
                    ->select(
                        // 'libro_id as libro_id',
                        'libros.titulo as libro',
                        \DB::raw('SUM(datos.unidades) as unidades'),
                        \DB::raw('SUM(datos.total) as total')
                    )
                    ->orderBy('libros.titulo', 'asc')
                    ->groupBy('libro_id', 'libros.titulo')
                    ->get();
        }
        if($categoria === 'NOTAS'){
            $datos = \DB::table('registers')
                    ->join('libros', 'registers.libro_id', '=', 'libros.id')
                    ->select(
                        'libros.titulo as libro',
                        \DB::raw('SUM(unidades) as unidades'),
                        \DB::raw('SUM(total) as total')
                    )->whereBetween('registers.created_at', [$fecha1, $fecha2])
                    ->orderBy('libros.titulo', 'asc')
                    ->groupBy('libro_id', 'libros.titulo')
                    ->get();
        }
        if($categoria === 'PEDIDOS'){
            // $datos = \DB::table('pedidos')
            //         ->join('libros', 'pedidos.libro_id', '=', 'libros.id')
            //         ->select(
            //             // 'libro_id as libro_id',
            //             'libros.titulo as libro',
            //             \DB::raw('SUM(unidades) as unidades'),
            //             \DB::raw('SUM(total) as total')
            //         )->whereBetween('pedidos.created_at', [$fecha1, $fecha2])
            //         ->orderBy('libros.titulo', 'asc')
            //         ->groupBy('libro_id', 'libros.titulo')
            //         ->get();
        }
        if($categoria === 'PROMOCIONES'){
            $datos = \DB::table('departures')
                    ->join('libros', 'departures.libro_id', '=', 'libros.id')
                    ->select('libros.titulo as libro', \DB::raw('SUM(unidades) as unidades'))
                    ->whereBetween('departures.created_at', [$fecha1, $fecha2])
                    ->orderBy('libros.titulo', 'asc')
                    ->groupBy('libro_id', 'libros.titulo')
                    ->get();
        }
        if($categoria === 'DONACIONES'){
            $datos = \DB::table('donaciones')
                    ->join('libros', 'donaciones.libro_id', '=', 'libros.id')
                    ->select('libros.titulo as libro', \DB::raw('SUM(unidades) as unidades'))
                    ->whereBetween('donaciones.created_at', [$fecha1, $fecha2])
                    ->orderBy('libros.titulo', 'asc')
                    ->groupBy('libro_id', 'libros.titulo')
                    ->get();
        }
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
}
