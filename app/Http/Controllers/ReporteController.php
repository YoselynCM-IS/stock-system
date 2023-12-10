<?php

namespace App\Http\Controllers;

use App\Exports\ventas\VentasDetalladoExport;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Reporte;
use App\User;
use Excel;

class ReporteController extends Controller
{
    public function lista(){
        return view('information.reportes.lista');
    }

    public function by_type_estado(Request $request){
        $reportes = $this->set_type($request)->paginate(20);
        return response()->json($reportes);
    }

    public function set_type($request){
        if($request->type == 'general')
            $reportes = Reporte::whereIn('type', ['cliente', 'proveedor']);
        else
            $reportes = Reporte::where('type', 'libro');
        
        return $reportes->with('user')->where('estado', $request->estado);
    }

    public function by_type_estado_fecha(Request $request){
        $reportes = $this->set_type($request)
                        ->where('created_at', 'like', '%'.$request->fecha.'%')        
                        ->paginate(20);
        return response()->json($reportes);
    }

    public function by_type_estado_usuario(Request $request){
        $reportes = $this->set_type($request)->where('user_id', $request->user_id)        
                        ->paginate(20);
        return response()->json($reportes);
    }

    public function get_datos_ventas(){
        return \DB::table('remisiones')
                ->join('clientes', 'remisiones.cliente_id', '=', 'clientes.id')
                ->join('datos', 'remisiones.id', '=', 'datos.remisione_id')
                ->join('libros', 'datos.libro_id', '=', 'libros.id')
                ->where('remisiones.estado', '!=', 'Cancelado')
                ->where('remisiones.deleted_at', NULL)
                ->where('datos.deleted_at', NULL);
    }

    public function select_datos_ventas($datos){
        return $datos->select(
            'remisiones.id as remisione_id', 
            'remisiones.cliente_id as cliente_id', 
            'clientes.tipo as cliente_tipo', 
            'clientes.name as cliente_name', 
            'remisiones.total as rem_total', 
            'remisiones.total_devolucion as rem_devolucion', 
            'remisiones.total_pagar as rem_pagar', 
            'remisiones.pagos as rem_pagos', 
            'remisiones.fecha_creacion as rem_fecha', 
            'remisiones.created_at as rem_created', 
            'datos.libro_id as libro_id', 
            'libros.editorial as libro_editorial', 
            'libros.type as libro_type', 
            'libros.ISBN as libro_isbn', 
            'libros.titulo as libro_titulo', 
            'datos.costo_unitario as dato_costo', 
            'datos.unidades as dato_unidades', 
            'datos.total as dato_total'
        )->orderBy('libro_editorial', 'asc')
        ->orderBy('libro_titulo', 'asc')->paginate(20);
    }

    public function ventas_lista($fi, $ff){
        // $ids = $datos->select('remisiones.id as remisione_id')->groupBy('remisione_id')->pluck('remisione_id');
        $fecha_actual = new Carbon('2022-12-01');
        // $fi = null;
        // $ff = null;
        $datos = $this->get_datos_ventas()->where('remisiones.fecha_creacion', 'like', '%'.$fecha_actual->format('Y-m').'%');
        $registros = $this->select_datos_ventas($datos);
        return view('information.reportes.ventas', compact('registros', 'fi', 'ff'));
    }

    public function ventas_by_fecha(Request $request){    
        // if($request->get('f_inicio') == null){
        //     $fecha_actual = Carbon::now();
        //     $anio_mes = $fecha_actual->format('Y-m-');
        //     $fi = $anio_mes.'01';
        //     $ff = $anio_mes.$fecha_actual->daysInMonth;
        // }
        $fi = $request->get('fi');
        $ff = $request->get('ff');
        $datos = $this->get_datos_ventas()->whereBetween('remisiones.fecha_creacion', [$fi, $ff]);
        $registros = $this->select_datos_ventas($datos);
        return view('information.reportes.ventas', compact('registros', 'fi', 'ff'));
    }

    public function down_ventas_by_fecha($fi, $ff){
        return Excel::download(new VentasDetalladoExport($fi, $ff), 'ventas.xlsx');
    }
}
