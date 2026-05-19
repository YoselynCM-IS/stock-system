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

        // 1. Subconsulta para Unidades de Remisiones por fecha
        $subDatos = \DB::table('datos')
            ->join('remisiones', 'datos.remisione_id', '=', 'remisiones.id')
            ->select('libro_id', \DB::raw('SUM(unidades) as unidades_remisiones'))
            ->whereNotIn('remisiones.estado', ['Cancelado'])
            ->whereNull('datos.deleted_at')
            ->whereBetween('datos.created_at', [$inicio, $final])
            ->groupBy('libro_id');

        // 2. Subconsulta para Unidades de Devoluciones por fecha (Con filtro de cancelados)
        $subDevoluciones = \DB::table('fechas')
            ->join('remisiones', 'fechas.remisione_id', '=', 'remisiones.id')
            ->select('fechas.libro_id', \DB::raw('SUM(fechas.unidades) as unidades_devoluciones'))
            ->whereNotIn('remisiones.estado', ['Cancelado']) // <-- Consistencia estricta
            ->where('fechas.unidades', '<>', 0)
            ->whereNull('fechas.deleted_at')
            ->whereBetween('fechas.created_at', [$inicio, $final])
            ->groupBy('fechas.libro_id');

        // 3. Consulta Principal
        $registros = \DB::table('libros as l')
            ->leftJoinSub($subDatos, 'd', 'l.id', '=', 'd.libro_id')
            ->leftJoinSub($subDevoluciones, 'dev', 'l.id', '=', 'dev.libro_id')
            ->select(
                'l.id as libro_id',
                'l.titulo as libro',
                \DB::raw('COALESCE(d.unidades_remisiones, 0) as unidades_remisiones'),
                \DB::raw('COALESCE(dev.unidades_devoluciones, 0) as unidades_devoluciones'),
                \DB::raw('(COALESCE(d.unidades_remisiones, 0) - COALESCE(dev.unidades_devoluciones, 0)) as unidades_vendidas')
            )
            // REGLA: Mostrar el libro si tiene remisiones > 0 O devoluciones > 0 en este rango de fechas
            ->where(function($query) {
                $query->whereRaw('COALESCE(d.unidades_remisiones, 0) > 0')
                    ->orWhereRaw('COALESCE(dev.unidades_devoluciones, 0) > 0');
            })
            ->orderBy('l.titulo', 'asc')
            ->paginate(20); 

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
        // 1. Subconsulta para agrupar unidades de remisiones
        $subDatos = \DB::table('datos')
            ->join('remisiones', 'datos.remisione_id', '=', 'remisiones.id')
            ->select('libro_id', \DB::raw('SUM(unidades) as unidades_remisiones'))
            ->whereNotIn('remisiones.estado', ['Cancelado'])
            ->whereNull('datos.deleted_at')
            ->groupBy('libro_id');

        // 2. Subconsulta para agrupar unidades de devoluciones (Se añade join para filtrar cancelados)
        $subDevoluciones = \DB::table('fechas')
            ->join('remisiones', 'fechas.remisione_id', '=', 'remisiones.id')
            ->select('fechas.libro_id', \DB::raw('SUM(fechas.unidades) as unidades_devoluciones'))
            ->whereNotIn('remisiones.estado', ['Cancelado']) // <-- Consistencia estricta
            ->where('fechas.unidades', '<>', 0)
            ->whereNull('fechas.deleted_at')
            ->groupBy('fechas.libro_id');

        // 3. Consulta principal
        $registros = \DB::table('libros as l')
            ->leftJoinSub($subDatos, 'd', 'l.id', '=', 'd.libro_id')
            ->leftJoinSub($subDevoluciones, 'dev', 'l.id', '=', 'dev.libro_id')
            ->select(
                'l.id as libro_id',
                'l.titulo as libro',
                \DB::raw('COALESCE(d.unidades_remisiones, 0) as unidades_remisiones'),
                \DB::raw('COALESCE(dev.unidades_devoluciones, 0) as unidades_devoluciones'),
                \DB::raw('(COALESCE(d.unidades_remisiones, 0) - COALESCE(dev.unidades_devoluciones, 0)) as unidades_vendidas')
            )
            // REGLA: Mostrar el libro si tiene remisiones > 0 O devoluciones > 0
            ->where(function($query) {
                $query->whereRaw('COALESCE(d.unidades_remisiones, 0) > 0')
                    ->orWhereRaw('COALESCE(dev.unidades_devoluciones, 0) > 0');
            })
            ->orderBy('l.titulo', 'asc')
            ->paginate(20);

        return response()->json($registros);
    }

    public function detallesULibro(Request $request){
        $libro_id = $request->libro_id;
        
        // Las fechas son opcionales (si vienes de getULibros van vacías; si vienes de byFechaULibros traen rango)
        $inicio = $request->inicio ? Carbon::parse($request->inicio)->startOfDay() : null;
        $final = $request->final ? Carbon::parse($request->final)->endOfDay() : null;

        // 1. Obtener Remisiones agrupadas por cliente
        $datos = \DB::table('datos')
            ->join('remisiones', 'datos.remisione_id', '=', 'remisiones.id')
            ->join('clientes', 'remisiones.cliente_id', '=', 'clientes.id')
            ->where('datos.libro_id', $libro_id)
            ->whereNotIn('remisiones.estado', ['Cancelado'])
            ->whereNull('datos.deleted_at')
            ->when($inicio && $final, function ($query) use ($inicio, $final) {
                return $query->whereBetween('datos.created_at', [$inicio, $final]);
            })
            ->select('clientes.id as cliente_id', 'clientes.name as cliente', \DB::raw('SUM(datos.unidades) as unidades'))
            ->groupBy('clientes.id', 'clientes.name')
            ->get()
            ->keyBy('cliente_id');

        // 2. Obtener Devoluciones agrupadas por cliente (Con filtro estricto de Cancelados)
        $devoluciones = \DB::table('fechas')
            ->join('remisiones', 'fechas.remisione_id', '=', 'remisiones.id')
            ->join('clientes', 'remisiones.cliente_id', '=', 'clientes.id')
            ->where('fechas.libro_id', $libro_id)
            ->whereNotIn('remisiones.estado', ['Cancelado']) // <-- Consistencia estricta
            ->where('fechas.unidades', '<>', 0)
            ->whereNull('fechas.deleted_at')
            ->when($inicio && $final, function ($query) use ($inicio, $final) {
                return $query->whereBetween('fechas.created_at', [$inicio, $final]);
            })
            ->select('clientes.id as cliente_id', 'clientes.name as cliente', \DB::raw('SUM(fechas.unidades) as unidades'))
            ->groupBy('clientes.id', 'clientes.name')
            ->get()
            ->keyBy('cliente_id');

        // 3. Fusionar los IDs de clientes para no perder a los que SOLO tienen devoluciones
        $todosClienteIds = $datos->keys()->merge($devoluciones->keys())->unique();

        // 4. Construir la respuesta mapeando ambos lados
        $vendidos = $todosClienteIds->map(function ($cliente_id) use ($datos, $devoluciones) {
            $remision = $datos->get($cliente_id);
            $devolucion = $devoluciones->get($cliente_id);

            $nombreCliente = $remision ? $remision->cliente : ($devolucion ? $devolucion->cliente : 'Desconocido');
            $u_rem = $remision ? (int)$remision->unidades : 0;
            $u_dev = $devolucion ? (int)$devolucion->unidades : 0;

            return [
                'cliente' => $nombreCliente,
                'unidades_remisiones' => $u_rem,
                'unidades_devoluciones' => $u_dev,
                'unidades_vendidas' => $u_rem - $u_dev
            ];
        })->values();

        // 5. Calcular Totales Globales (Ahora serán idénticos a los del listado principal)
        $totales = [
            'total_remisiones' => $vendidos->sum('unidades_remisiones'),
            'total_devoluciones' => $vendidos->sum('unidades_devoluciones'),
            'total_vendidas' => $vendidos->sum('unidades_vendidas'),
        ];

        return response()->json([
            'detalles' => $vendidos,
            'totales' => $totales
        ]);
    }

    public function download_ulibros(Request $request){
        // 1. Subconsulta base para Unidades de Remisiones
        $subDatos = \DB::table('datos')
            ->join('remisiones', 'datos.remisione_id', '=', 'remisiones.id')
            ->select('libro_id', \DB::raw('SUM(unidades) as unidades_remisiones'))
            ->whereNotIn('remisiones.estado', ['Cancelado'])
            ->whereNull('datos.deleted_at');

        // 2. Subconsulta base para Unidades de Devoluciones (Con consistencia de cancelados)
        $subDevoluciones = \DB::table('fechas')
            ->join('remisiones', 'fechas.remisione_id', '=', 'remisiones.id')
            ->select('fechas.libro_id', \DB::raw('SUM(fechas.unidades) as unidades_devoluciones'))
            ->whereNotIn('remisiones.estado', ['Cancelado'])
            ->where('fechas.unidades', '<>', 0)
            ->whereNull('fechas.deleted_at');

        // 3. Evaluamos de forma opcional si el b-button envió parámetros de fecha en la URL
        if ($request->filled('inicio') && $request->filled('final')) {
            $inicio = Carbon::parse($request->inicio)->startOfDay();
            $final = Carbon::parse($request->final)->endOfDay();

            $subDatos->whereBetween('datos.created_at', [$inicio, $final]);
            $subDevoluciones->whereBetween('fechas.created_at', [$inicio, $final]);
        }

        // Agrupamos después de aplicar las fechas opcionales
        $subDatos->groupBy('libro_id');
        $subDevoluciones->groupBy('fechas.libro_id');

        // 4. Consulta unificada (Lógica idéntica a tus funciones de listado)
        $queryPrincipal = \DB::table('libros as l')
            ->leftJoinSub($subDatos, 'd', 'l.id', '=', 'd.libro_id')
            ->leftJoinSub($subDevoluciones, 'dev', 'l.id', '=', 'dev.libro_id')
            ->select(
                'l.id as libro_id',
                'l.ISBN as isbn',
                'l.titulo as libro',
                \DB::raw('COALESCE(d.unidades_remisiones, 0) as unidades_remisiones'),
                \DB::raw('COALESCE(dev.unidades_devoluciones, 0) as unidades_devoluciones'),
                \DB::raw('(COALESCE(d.unidades_remisiones, 0) - COALESCE(dev.unidades_devoluciones, 0)) as unidades_vendidas')
            )
            ->where(function($query) {
                $query->whereRaw('COALESCE(d.unidades_remisiones, 0) > 0')
                    ->orWhereRaw('COALESCE(dev.unidades_devoluciones, 0) > 0');
            })
            ->orderBy('l.titulo', 'asc');

        // 5. Retorna la descarga directa del Excel conservando el objetivo unificado
        return Excel::download(new ULibrosExport($queryPrincipal), 'unidades_libros.xlsx');
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
