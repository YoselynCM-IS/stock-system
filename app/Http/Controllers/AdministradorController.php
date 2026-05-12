<?php

namespace App\Http\Controllers;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\UnidadesExport;
use App\Exports\ULibrosExport;
use Illuminate\Http\Request;
use App\Enteditoriale;
use App\Remcliente;
use App\Promotion;
use App\Remisione;
use App\Entrada;
use App\Cliente;
use App\Regalo;
use App\Order;
use App\Libro;
use App\Note;
use Carbon\Carbon;

class AdministradorController extends Controller
{

    // OBTENER VENTAS POR FECHAS
    public function byFechaULibros(Request $request){
        $inicio = Carbon::parse($request->inicio)->startOfDay();
        $final = Carbon::parse($request->final)->endOfDay();

        // 1. Subconsulta para Unidades de Remisiones
        $subDatos = \DB::table('datos')
            ->join('remisiones', 'datos.remisione_id', '=', 'remisiones.id')
            ->select('libro_id', \DB::raw('SUM(unidades) as unidades_remisiones'))
            ->whereNotIn('remisiones.estado', ['Cancelado'])
            ->whereNull('datos.deleted_at')
            ->whereBetween('datos.created_at', [$inicio, $final])
            ->groupBy('libro_id');

        // 2. Subconsulta para Unidades de Devoluciones
        $subDevoluciones = \DB::table('fechas')
            ->select('libro_id', \DB::raw('SUM(unidades) as unidades_devoluciones'))
            ->whereNull('fechas.deleted_at')
            ->whereBetween('fechas.created_at', [$inicio, $final])
            ->groupBy('libro_id');

        // 3. Consulta Principal con Paginación
        $registros = \DB::table('libros as l')
            ->joinSub($subDatos, 'd', function ($join) {
                $join->on('l.id', '=', 'd.libro_id');
            })
            ->leftJoinSub($subDevoluciones, 'dev', function ($join) {
                $join->on('l.id', '=', 'dev.libro_id');
            })
            ->select(
                'l.id as libro_id',
                'l.titulo as libro',
                \DB::raw('COALESCE(d.unidades_remisiones, 0) as unidades_remisiones'),
                \DB::raw('COALESCE(dev.unidades_devoluciones, 0) as unidades_devoluciones'),
                \DB::raw('(COALESCE(d.unidades_remisiones, 0) - COALESCE(dev.unidades_devoluciones, 0)) as unidades_vendidas')
            )
            ->orderBy('l.id', 'asc')
            ->paginate(15); // Cambia el 15 por el número de registros por página que desees

        return response()->json($registros);
    }

    public function remisiones() {
        return view('administrador.remisiones');
    }

    public function pagos(){
        return view('administrador.pagos');
    }
    
    public function registrar_pago(){
        $responsables = \DB::table('responsables')->orderBy('responsable', 'asc')->get();
        return view('administrador.registrar_pago', compact('responsables'));
    }

    public function libros(){
        $editoriales = \DB::table('editoriales')->orderBy('editorial', 'asc')->get();
        return view('administrador.libros', compact('editoriales'));
    }

    public function entradas(){
        $editoriales = \DB::table('editoriales')->orderBy('editorial', 'asc')->get();
        return view('administrador.entradas', compact('editoriales'));
    }

    public function notas(){
        return view('administrador.notas');
    }

    public function pedidos(){
        return view('administrador.pedidos');
    }

    public function donaciones(){
        return view('administrador.donaciones');
    }

    public function clientes(){
        return view('administrador.clientes');
    }

    public function movimientos(){
        $editoriales = \DB::table('editoriales')->orderBy('editorial', 'asc')->get();
        return view('administrador.movimientos', compact('editoriales'));
    }

    public function movimientos_monto(){
        $editoriales = \DB::table('editoriales')->orderBy('editorial', 'asc')->get();
        return view('administrador.movmonto', compact('editoriales'));
    }

    public function unidades(){
        return view('administrador.unidades');
    }
    // MOSTRAR LAS UNIDADES COMPRADAS POR CLIENTE
    public function getUnidades(){
        $registros = $this->funGral();
        return response()->json($registros);
    }

    // MOSTRAR DETALLES DE LAS UNIDADES
    public function detallesUnidades(Request $request){
        $cliente_id = $request->cliente_id;
        $datos = \DB::table('libros')
                ->join('datos', 'libros.id', '=', 'datos.libro_id')
                ->join('remisiones', 'datos.remisione_id', '=', 'remisiones.id')
                ->whereNotIn('remisiones.estado', ['Cancelado'])
                ->whereNull('datos.deleted_at')
                ->where('remisiones.cliente_id', $cliente_id)
                ->select('libros.titulo as libro', \DB::raw('SUM(datos.unidades) as unidades_remisiones'))
                ->groupBy('libros.titulo')
                ->get();
        $devoluciones = \DB::table('libros')
                ->join('devoluciones', 'libros.id', '=', 'devoluciones.libro_id')
                ->join('remisiones', 'devoluciones.remisione_id', '=', 'remisiones.id')
                ->whereNotIn('remisiones.estado', ['Cancelado'])
                ->whereNotIn('devoluciones.unidades', [0])
                ->where('remisiones.cliente_id', $cliente_id)
                ->select('libros.titulo as libro', \DB::raw('SUM(devoluciones.unidades) as unidades_devoluciones'))
                ->groupBy('libros.titulo')
                ->get();
        $vendidos = array();
        foreach($datos as $dato){
            $registro = [ 
                'libro' => $dato->libro, 
                'unidades_remisiones' => $dato->unidades_remisiones,
                'unidades_devoluciones' => 0,
                'unidades_vendidas' => $dato->unidades_remisiones
            ];
            foreach($devoluciones as $devolucione){
                if($dato->libro == $devolucione->libro){
                    $registro['unidades_devoluciones'] = $devolucione->unidades_devoluciones;
                    $registro['unidades_vendidas'] = $dato->unidades_remisiones - $devolucione->unidades_devoluciones;
                }
            }
            array_push($vendidos, $registro);
        }

        return response()->json($vendidos);
    }

    public function funGral(){
        $clientes = \DB::table('clientes')->orderBy('name', 'asc')->get();
        $datos = \DB::table('datos')
                ->join('remisiones', 'datos.remisione_id', '=', 'remisiones.id')
                ->whereNotIn('remisiones.estado', ['Cancelado'])
                ->whereNull('datos.deleted_at')
                ->select(
                    'remisiones.cliente_id as cliente_id',
                    \DB::raw('SUM(datos.unidades) as unidades_remisiones')
                )->groupBy('remisiones.cliente_id')
                ->orderBy('remisiones.cliente_id', 'asc')
                ->get();
        $devoluciones = \DB::table('devoluciones')
                ->join('remisiones', 'devoluciones.remisione_id', '=', 'remisiones.id')
                ->whereNotIn('remisiones.estado', ['Cancelado'])
                ->whereNotIn('devoluciones.unidades', [0])
                ->select(
                    'remisiones.cliente_id as cliente_id',
                    \DB::raw('SUM(devoluciones.unidades) as unidades_devoluciones')
                )->groupBy('remisiones.cliente_id')
                ->orderBy('remisiones.cliente_id', 'asc')
                ->get();

        $registers = array();
        foreach($clientes as $cliente){
            $registro = [
                'cliente_id' => $cliente->id,
                'cliente' => $cliente->name,
                'unidades_remisiones' => 0,
                'unidades_devoluciones' => 0,
                'unidades_vendidas' => 0
            ];
            
            foreach($datos as $dato){
                if($dato->cliente_id == $cliente->id) 
                    $registro['unidades_remisiones'] = $dato->unidades_remisiones;
            }
            foreach($devoluciones as $devolucione){
                if($devolucione->cliente_id == $cliente->id) 
                    $registro['unidades_devoluciones'] = $devolucione->unidades_devoluciones;
            }
            $registro['unidades_vendidas'] = $registro['unidades_remisiones'] - $registro['unidades_devoluciones'];
            array_push($registers, $registro);
        }

        $registros = array();
        foreach($registers as $register){
            if($register['unidades_remisiones'] > 0)
                array_push($registros, $register);
        }

        return $registros;
    }

    public function download_unidades(){
        return Excel::download(new UnidadesExport, 'unidades.xlsx');
    }

    public function unidades_libro(){
        return view('administrador.unidades_libro');
    }

    public function getULibros() {
        // 1. Subconsulta para agrupar unidades de remisiones (datos)
        $subDatos = \DB::table('datos')
            ->join('remisiones', 'datos.remisione_id', '=', 'remisiones.id')
            ->select('libro_id', \DB::raw('SUM(unidades) as unidades_remisiones'))
            ->whereNotIn('remisiones.estado', ['Cancelado'])
            ->whereNull('datos.deleted_at')
            ->groupBy('libro_id');

        // 2. Subconsulta para agrupar unidades de devoluciones
        $subDevoluciones = \DB::table('devoluciones')
            ->select('libro_id', \DB::raw('SUM(unidades) as unidades_devoluciones'))
            ->where('unidades', '<>', 0) // Optimizamos el whereNotIn por un <>
            ->groupBy('libro_id');

        // 3. Consulta principal uniendo libros con las subconsultas
        $registros = \DB::table('libros as l')
            // Usamos joinSub (INNER JOIN) porque tu código original solo retorna 
            // libros que tienen unidades_remisiones > 0
            ->joinSub($subDatos, 'd', function ($join) {
                $join->on('l.id', '=', 'd.libro_id');
            })
            // Usamos leftJoinSub porque un libro puede tener ventas pero NO devoluciones
            ->leftJoinSub($subDevoluciones, 'dev', function ($join) {
                $join->on('l.id', '=', 'dev.libro_id');
            })
            ->select(
                'l.id as libro_id',
                'l.titulo as libro',
                \DB::raw('COALESCE(d.unidades_remisiones, 0) as unidades_remisiones'),
                \DB::raw('COALESCE(dev.unidades_devoluciones, 0) as unidades_devoluciones'),
                \DB::raw('(COALESCE(d.unidades_remisiones, 0) - COALESCE(dev.unidades_devoluciones, 0)) as unidades_vendidas')
            )
            ->orderBy('l.id', 'asc')
            ->paginate(20); // Aquí defines cuántos libros por página quieres (ej. 20)

        return response()->json($registros);
    }

    public function detallesULibro(Request $request){
        $libro_id = $request->libro_id;
        
        // Validamos fechas
        $inicio = $request->inicio ? Carbon::parse($request->inicio)->startOfDay() : null;
        $final = $request->final ? Carbon::parse($request->final)->endOfDay() : null;

        // 1. Obtener Remisiones
        $datos = \DB::table('datos')
            ->join('remisiones', 'datos.remisione_id', '=', 'remisiones.id')
            ->join('clientes', 'remisiones.cliente_id', '=', 'clientes.id')
            ->where('libro_id', $libro_id)
            ->whereNotIn('remisiones.estado', ['Cancelado'])
            ->whereNull('datos.deleted_at')
            ->when($inicio && $final, function ($query) use ($inicio, $final) {
                return $query->whereBetween('datos.created_at', [$inicio, $final]);
            })
            ->select('clientes.id as cliente_id', 'clientes.name as cliente', \DB::raw('SUM(unidades) as unidades'))
            ->groupBy('clientes.id', 'clientes.name')
            ->get();

        // 2. Obtener Devoluciones indexadas por cliente_id
        $devoluciones = \DB::table('fechas')
            ->join('remisiones', 'fechas.remisione_id', '=', 'remisiones.id')
            ->where('fechas.libro_id', $libro_id)
            ->where('fechas.unidades', '<>', 0)
            ->whereNull('fechas.deleted_at')
            ->when($inicio && $final, function ($query) use ($inicio, $final) {
                return $query->whereBetween('fechas.created_at', [$inicio, $final]);
            })
            ->select('remisiones.cliente_id', \DB::raw('SUM(fechas.unidades) as unidades'))
            ->groupBy('remisiones.cliente_id')
            ->get()
            ->keyBy('cliente_id');

        // 3. Mapear resultados por cliente
        $vendidos = $datos->map(function ($dato) use ($devoluciones) {
            $devolucion = $devoluciones->get($dato->cliente_id);
            $u_dev = $devolucion ? (int)$devolucion->unidades : 0;
            $u_rem = (int)$dato->unidades;

            return [
                'cliente' => $dato->cliente,
                'unidades_remisiones' => $u_rem,
                'unidades_devoluciones' => $u_dev,
                'unidades_vendidas' => $u_rem - $u_dev
            ];
        });

        // 4. Calcular Totales Globales
        $totales = [
            'total_remisiones' => $vendidos->sum('unidades_remisiones'),
            'total_devoluciones' => $vendidos->sum('unidades_devoluciones'),
            'total_vendidas' => $vendidos->sum('unidades_vendidas'),
        ];

        // Retornamos ambos para que el componente tenga todo
        return response()->json([
            'detalles' => $vendidos,
            'totales' => $totales
        ]);
    }

    public function download_ulibros(){
        return Excel::download(new ULibrosExport, 'unidades.xlsx');
    }

    public function comparativa(){
        return view('administrador.comparativa');
    }

    // FECHA DE ADEUDOS DE REMISIONES
    public function fecha_adeudo(){
        return view('administrador.fecha-adeudo');
    }
    
    // PAGOS DE ENTRADAS
    public function entradas_pagos(){
        $editoriales = Enteditoriale::orderBy('editorial', 'asc')
                        ->withCount('entdepositos')->get();
        return view('administrador.entradas.pagos', compact('editoriales'));
    }

    public function entradas_salidas(){
        return view('administrador.entradas-salidas');
    }

    public function salidas(){
        return view('administrador.salidas');
    }

    // CERRAR REMISIONES
    public function cerrar(){
        $responsables = \DB::table('responsables')->orderBy('responsable', 'asc')->get();
        return view('administrador.cerrar', compact('responsables'));
    }

    public function codes(){
        return view('administrador.codes');
    }
}
