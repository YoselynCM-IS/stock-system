<?php

namespace App\Exports\EntSal;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\Libro;

class EntSalExport implements FromView
{
    public function __construct($editorial, $de, $a)
    {
        $this->editorial = $editorial;
        $this->de = $de;
        $this->a = $a;
    }

    public function view(): View
    {
        $movimientos = $this->entradas_salidas();
        return view('download.excel.movimientos.entsal', [
            'movimientos' => $movimientos
        ]);
    }

    public function entradas_salidas(){
        $inicio = $this->de.' 00:00:00';
        $final = $this->a.' 23:59:59';
        // ENTRADAS
        $entradas = \DB::table('registros')
                    ->join('entradas', 'registros.entrada_id', '=', 'entradas.id')
                    ->join('libros', 'registros.libro_id', '=', 'libros.id')
                    ->whereBetween('entradas.created_at', [$inicio, $final])
                    ->select('registros.libro_id', 'registros.unidades')
                    ->get();
        $fechas = \DB::table('fechas')
                    ->join('libros', 'fechas.libro_id', '=', 'libros.id')
                    ->whereBetween('fechas.fecha_devolucion', [$inicio, $final])
                    ->select('fechas.libro_id', 'fechas.unidades')
                    ->get();
        // SALIDAS
        $salidas = \DB::table('sregistros')
                    ->join('salidas', 'sregistros.salida_id', '=', 'salidas.id')
                    ->where('salidas.estado', 'enviado')
                    ->whereBetween('sregistros.created_at', [$inicio, $final])
                    ->select('libro_id as libro_id', 'sregistros.unidades')
                    ->get();
        $entdevoluciones = \DB::table('entdevoluciones')
                    ->join('registros', 'entdevoluciones.registro_id', '=', 'registros.id')
                    ->join('libros', 'registros.libro_id', '=', 'libros.id')
                    ->whereBetween('entdevoluciones.created_at', [$inicio, $final])
                    ->select('registros.libro_id', 'entdevoluciones.unidades')
                    ->get();
        $remisiones = \DB::table('datos')
                    ->join('remisiones', 'datos.remisione_id', '=', 'remisiones.id')
                    ->join('libros', 'datos.libro_id', '=', 'libros.id')
                    ->whereBetween('remisiones.created_at', [$inicio, $final])
                    ->whereNotIn('remisiones.estado', ['Cancelado'])
                    ->whereNull('datos.deleted_at')
                    ->select('datos.libro_id', 'datos.unidades')
                    ->get();
        $notas = \DB::table('registers')
                    ->join('notes', 'registers.note_id', '=', 'notes.id')
                    ->join('libros', 'registers.libro_id', '=', 'libros.id')
                    ->whereBetween('notes.created_at', [$inicio, $final])
                    ->select('registers.libro_id', 'registers.unidades')
                    ->get();
        $promociones = \DB::table('departures')
                    ->join('promotions', 'departures.promotion_id', '=', 'promotions.id')
                    ->join('libros', 'departures.libro_id', '=', 'libros.id')
                    ->whereBetween('promotions.created_at', [$inicio, $final])
                    ->select('departures.libro_id', 'departures.unidades')
                    ->get();
        $donaciones = \DB::table('donaciones')
                    ->join('regalos', 'donaciones.regalo_id', '=', 'regalos.id')
                    ->join('libros', 'donaciones.libro_id', '=', 'libros.id')
                    ->whereBetween('regalos.created_at', [$inicio, $final])
                    ->select('donaciones.libro_id', 'donaciones.unidades')
                    ->get();

        $ids = [];
        $ids = $this->get_ids_libros($entradas, $ids);
        $ids = $this->get_ids_libros($fechas, $ids);
        $ids = $this->get_ids_libros($salidas, $ids);
        $ids = $this->get_ids_libros($entdevoluciones, $ids);
        $ids = $this->get_ids_libros($remisiones, $ids);
        $ids = $this->get_ids_libros($notas, $ids);
        $ids = $this->get_ids_libros($promociones, $ids);
        $ids = $this->get_ids_libros($donaciones, $ids);
        
        $lista_datos = [];
        $libros = Libro::where('editorial', $this->editorial)
                    ->whereIn('id', $ids)->orderBy('titulo', 'asc')->get();
        $libros->map(function($libro) use(&$lista_datos, $entradas, $fechas, $salidas, $entdevoluciones, $remisiones, $notas, $promociones, $donaciones){
            $ter = $this->get_datos_libros($libro->id, $entradas);
            $tdf = $this->get_datos_libros($libro->id, $fechas);
            $tss = $this->get_datos_libros($libro->id, $salidas);
            $ted = $this->get_datos_libros($libro->id, $entdevoluciones);
            $trr = $this->get_datos_libros($libro->id, $remisiones);
            $tnr = $this->get_datos_libros($libro->id, $notas);
            $tpd = $this->get_datos_libros($libro->id, $promociones);
            $trd = $this->get_datos_libros($libro->id, $donaciones);
            
            $datos = [
                'id' => $libro->id,
                'libro' => $libro->titulo,
                'entradas' => $ter,
                'devoluciones' => $tdf,
                'salidas' => $tss,
                'entdevoluciones' => $ted,
                'remisiones' => $trr,
                'notas' => $tnr,
                'promociones' => $tpd,
                'donaciones' => $trd
            ];
            
            $lista_datos[] = $datos;
        });
        return $lista_datos;
    }

    public function get_ids_libros($array, $ids){
        $array->map(function($a) use(&$ids){
            $ids[] = $a->libro_id;
        });
        return $ids;
    }

    public function get_datos_libros($libro_id, $array){
        $dato = 0;
        foreach ($array as $a) {
            if($libro_id == $a->libro_id) $dato += $a->unidades;
        }
        return $dato;
    }
}
