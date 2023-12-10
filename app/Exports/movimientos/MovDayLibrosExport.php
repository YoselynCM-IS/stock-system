<?php

namespace App\Exports\movimientos;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\Libro;

class MovDayLibrosExport implements FromView
{
    public function __construct($de, $a) {
        $this->de = $de;
        $this->a = $a;
    }

    public function view(): View {
        return view('download.excel.movimientos.entsal-detallado', [
            // 'fecha' => $this->fecha,
            'movimientos' => $this->entradas_salidas()
        ]);
    }

    // LISTA GENERAL
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
        $saldevoluciones = \DB::table('saldevoluciones')
                    ->whereBetween('saldevoluciones.created_at', [$inicio, $final])
                    ->select('saldevoluciones.libro_id', 'saldevoluciones.unidades')
                    ->get();
        $prodevoluciones = \DB::table('prodevoluciones')
                    ->whereBetween('prodevoluciones.created_at', [$inicio, $final])
                    ->select('prodevoluciones.libro_id', 'prodevoluciones.unidades')
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
                    ->whereNotIn('promotions.estado', ['Cancelado'])
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
        $ids = $this->get_ids_libros($saldevoluciones, $ids);
        $ids = $this->get_ids_libros($prodevoluciones, $ids);
        $ids = $this->get_ids_libros($salidas, $ids);
        $ids = $this->get_ids_libros($entdevoluciones, $ids);
        $ids = $this->get_ids_libros($remisiones, $ids);
        $ids = $this->get_ids_libros($notas, $ids);
        $ids = $this->get_ids_libros($promociones, $ids);
        $ids = $this->get_ids_libros($donaciones, $ids);
        
        $lista_datos = [];
        $libros = Libro::whereIn('id', $ids)->orderBy('titulo', 'asc')->get();
        $libros->map(function($libro) use(&$lista_datos, $entradas, $fechas, $saldevoluciones, $prodevoluciones, $salidas, $entdevoluciones, $remisiones, $notas, $promociones, $donaciones){
            $ter = $this->get_datos_libros($libro->id, $entradas);
            $tdf = $this->get_datos_libros($libro->id, $fechas);
            $tsd = $this->get_datos_libros($libro->id, $saldevoluciones);
            $tdd = $this->get_datos_libros($libro->id, $prodevoluciones);
            $tss = $this->get_datos_libros($libro->id, $salidas);
            $ted = $this->get_datos_libros($libro->id, $entdevoluciones);
            $trr = $this->get_datos_libros($libro->id, $remisiones);
            $tnr = $this->get_datos_libros($libro->id, $notas);
            $tpd = $this->get_datos_libros($libro->id, $promociones);
            $trd = $this->get_datos_libros($libro->id, $donaciones);
            
            $datos = [
                'isbn' => $libro->ISBN,
                'libro' => $libro->titulo,
                'entradas' => $ter,
                'devoluciones' => $tdf,
                'saldevoluciones' => $tsd,
                'prodevoluciones' => $tdd,
                'salidas' => $tss,
                'entdevoluciones' => $ted,
                'remisiones' => $trr,
                'notas' => $tnr,
                'promociones' => $tpd,
                'donaciones' => $trd,
                'detalles' => $this->details_entsal($libro->id)
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

    public function details_entsal($libro_id){
        $inicio = $this->de.' 00:00:00';
        $final = $this->a.' 23:59:59';
        // ENTRADAS
        $entradas = \DB::table('registros')
                    ->join('entradas', 'registros.entrada_id', '=', 'entradas.id')
                    ->where('registros.libro_id', $libro_id)
                    ->whereBetween('entradas.created_at', [$inicio, $final])
                    ->select('entradas.folio as folio', 'registros.unidades')
                    ->get();
        $fechas = \DB::table('fechas')
                    ->join('remisiones', 'fechas.remisione_id', '=', 'remisiones.id')
                    ->join('clientes', 'remisiones.cliente_id', '=', 'clientes.id')
                    ->where('fechas.libro_id', $libro_id)
                    ->whereBetween('fechas.fecha_devolucion', [$inicio, $final])
                    ->select('clientes.name as cliente', 'fechas.remisione_id as folio', 'fechas.unidades')
                    ->get();
        $saldevoluciones = \DB::table('saldevoluciones')
                    ->join('salidas', 'saldevoluciones.salida_id', '=', 'salidas.id')
                    ->where('saldevoluciones.libro_id', $libro_id)
                    ->whereBetween('saldevoluciones.created_at', [$inicio, $final])
                    ->select('salidas.folio as folio', 'saldevoluciones.unidades')
                    ->get();
        $prodevoluciones = \DB::table('prodevoluciones')
                    ->join('promotions', 'prodevoluciones.promotion_id', '=', 'promotions.id')
                    ->where('libro_id', $libro_id)
                    ->whereBetween('prodevoluciones.created_at', [$inicio, $final])
                    ->select('promotions.folio as folio', 'prodevoluciones.unidades')
                    ->get();
        // SALIDAS
        $salidas = \DB::table('sregistros')
                    ->join('salidas', 'sregistros.salida_id', '=', 'salidas.id')
                    ->where('sregistros.libro_id', $libro_id)
                    ->where('salidas.estado', 'enviado')
                    ->whereBetween('sregistros.created_at', [$inicio, $final])
                    ->select('salidas.folio as folio', 'sregistros.unidades')
                    ->get();
        $entdevoluciones = \DB::table('entdevoluciones')
                    ->join('registros', 'entdevoluciones.registro_id', '=', 'registros.id')
                    ->join('entradas', 'registros.entrada_id', '=', 'entradas.id')
                    ->join('libros', 'registros.libro_id', '=', 'libros.id')
                    ->where('registros.libro_id', $libro_id)
                    ->whereBetween('entdevoluciones.created_at', [$inicio, $final])
                    ->select('entradas.folio as folio', 'entdevoluciones.unidades')
                    ->get();
        $remisiones = \DB::table('datos')
                    ->join('remisiones', 'datos.remisione_id', '=', 'remisiones.id')
                    ->join('clientes', 'remisiones.cliente_id', '=', 'clientes.id')
                    ->where('datos.libro_id', $libro_id)
                    ->whereNotIn('remisiones.estado', ['Cancelado'])
                    ->whereNull('datos.deleted_at')
                    ->whereBetween('remisiones.created_at', [$inicio, $final])
                    ->select('clientes.name as cliente', 'remisiones.id as folio', 'datos.unidades')
                    ->get();
        $notas = \DB::table('registers')
                    ->join('notes', 'registers.note_id', '=', 'notes.id')
                    ->where('registers.libro_id', $libro_id)
                    ->whereBetween('notes.created_at', [$inicio, $final])
                    ->select('notes.folio as folio', 'registers.unidades')
                    ->get();
        $promociones = \DB::table('departures')
                    ->join('promotions', 'departures.promotion_id', '=', 'promotions.id')
                    ->where('departures.libro_id', $libro_id)
                    ->whereBetween('promotions.created_at', [$inicio, $final])
                    ->whereNotIn('promotions.estado', ['Cancelado'])
                    ->select('promotions.folio as folio', 'departures.unidades')
                    ->get();
        $donaciones = \DB::table('donaciones')
                    ->join('regalos', 'donaciones.regalo_id', '=', 'regalos.id')
                    ->where('donaciones.libro_id', $libro_id)
                    ->whereBetween('regalos.created_at', [$inicio, $final])
                    ->select('regalos.plantel as folio', 'donaciones.unidades')
                    ->get();

        return [
            'entradas' => $entradas,
            'devoluciones' => $fechas,
            'saldevoluciones' => $saldevoluciones,
            'prodevoluciones' => $prodevoluciones,
            'salidas' => $salidas,
            'entdevoluciones' => $entdevoluciones,
            'remisiones' => $remisiones,
            'notas' => $notas,
            'promociones' => $promociones,
            'donaciones' => $donaciones,
        ];
    }
}
