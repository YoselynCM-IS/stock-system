<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Remdeposito;
use App\Remcliente;
use App\Remisione;
use App\Cctotale;
use App\Deposito;
use App\Corte;

class RemclienteController extends Controller
{
    // MOSTRAR TODAS LAS REMCLIENTE
    public function index(){
        $remclientes = \DB::table('remclientes')
                        ->join('clientes', 'remclientes.cliente_id', '=', 'clientes.id')
                        ->select('clientes.id as cliente_id', 'clientes.name as name', 'total', 'total_devolucion', 'total_pagos', 'total_pagar')
                        ->where('total', '>', 0)
                        ->orderBy('clientes.name', 'asc')
                        ->paginate(20);
        return response()->json($remclientes);
    }

    // MOSTRAR PAGOS POR CLIENTE
    public function by_cliente(Request $request){
        $cliente_id = $request->cliente_id;
        $remcliente = \DB::table('remclientes')
                        ->join('clientes', 'remclientes.cliente_id', '=', 'clientes.id')
                        ->select('clientes.id as cliente_id', 'clientes.name as name', 'total', 'total_devolucion', 'total_pagos', 'total_pagar')
                        ->where('cliente_id', $cliente_id)
                        ->where('total', '>', 0)
                        ->paginate(1);
        return response()->json($remcliente);
    }

    // OBTENER TOTALES
    public function get_totales(){
        $totales = Remcliente::select(
            \DB::raw('SUM(total) as total'),
            \DB::raw('SUM(total_devolucion) as total_devolucion'),
            \DB::raw('SUM(total_pagos) as total_pagos'),
            \DB::raw('SUM(total_pagar) as total_pagar')
        )->get();
        return response()->json($totales);
    }

    // OBTENER COTEJO DE LA CUENTA GENERAL CON LA DEL CLIENTE
    public function get_gralcortes(){
        $remclientes = \DB::table('remclientes')
                        ->join('clientes', 'remclientes.cliente_id', '=', 'clientes.id')
                        ->select('clientes.id as cliente_id', 'clientes.name as name', 'remclientes.id', 'total', 'total_devolucion', 'total_pagos', 'total_pagar')
                        ->where('total', '>', 0)
                        ->orderBy('clientes.name', 'asc')
                        ->get();
        
        $datos = [];
        $remclientes->map(function($remcliente) use(&$datos){
            $datos[] = $this->set_cctotales($remcliente, $remcliente->name);
        });
        return response()->json($datos);
    }

    // OBTENER CUENTA POR CLIENTE
    public function gc_bycliente(Request $request){
        $remcliente = Remcliente::where('cliente_id', $request->cliente_id)
                                    ->where('total', '>', 0)->first();
        $datos = $this->set_cctotales($remcliente, $remcliente->cliente->name);
        return response()->json($datos);
    }

    // ACOMODAR DATOS
    public function set_cctotales($remcliente, $cliente){
        $cctotales = Cctotale::where('cliente_id', $remcliente->cliente_id)
                            ->select(
                                \DB::raw('SUM(total) as total'),
                                \DB::raw('SUM(total_devolucion) as total_devolucion'),
                                \DB::raw('SUM(total_pagos) as total_pagos'),
                                \DB::raw('SUM(total_pagar) as total_pagar')
                            )->get();
        
        $cortes = Corte::orderBy('inicio', 'desc')->get();
        
        $lista = [];
        $cortes->map(function($corte) use(&$lista, $remcliente){
            $cctotale = Cctotale::where('cliente_id', $remcliente->cliente_id)
                                ->where('corte_id', $corte->id)->first();
            $remdepositos = Remdeposito::where('remcliente_id', $remcliente->id)
                        ->where('corte_id', $corte->id)
                        ->select(\DB::raw('SUM(pago) as pago'))->get();
            $depositos = Remisione::where('corte_id', $corte->id)
                        ->join('depositos', 'remisiones.id', '=', 'depositos.remisione_id')
                        ->where('cliente_id', $remcliente->cliente_id)
                        ->whereNotIn('estado', ['Iniciado', 'Cancelado'])
                        ->select(
                            \DB::raw('SUM(pago) as pago')
                        )->get();
            $remisiones = Remisione::where('corte_id', $corte->id)
                        ->where('cliente_id', $remcliente->cliente_id)
                        ->whereNotIn('estado', ['Iniciado', 'Cancelado'])
                        ->select(
                            \DB::raw('SUM(total) as total'),
                            \DB::raw('SUM(total_devolucion) as total_devolucion')
                        )->get();

            $total = $remisiones[0]['total'];
            $total_pagos = $remdepositos[0]['pago'] + $depositos[0]['pago'];
            $total_devolucion = $remisiones[0]['total_devolucion'];
            if($cctotale != null){
                $cta = $total == $cctotale->total;
                $ctp = $total_pagos == $cctotale->total_pagos;
                $ctd = $total_devolucion == $cctotale->total_devolucion;
            } else {
                $cta = true;
                $ctp = true;
                $ctd = true;
            }
            $c = [
                'corte' => 'Temporada '.$corte->tipo.' '.$corte->inicio.'-'.$corte->final,
                'total' => $total,
                'total_pagos' => $total_pagos,
                'total_devolucion' => $total_devolucion,
                'cta' => $cta,
                'ctp' => $ctp,
                'ctd' => $ctd
            ];
            $lista[] = $c;
        });
        

        $check_total = (double)$remcliente->total - ((double)$remcliente->total_devolucion + (double)$remcliente->total_pagos);
        $ta = $remcliente->total;
        $td = $remcliente->total_devolucion;
        $tp = $remcliente->total_pagos;
        $tr = $remcliente->total_pagar;
        $ct = $cctotales[0];
        return [
            'cliente_id' => $remcliente->cliente_id, 
            'name' => $cliente, 
            'total' => $ta, 
            'total_devolucion' => $td, 
            'total_pagos' => $tp, 
            'total_pagar' => $tr,
            '_cellVariants'=> [
                'name' => $check_total !== $tr ? 'danger':'',
                'total' => $ta !== $ct['total'] ? 'danger':'', 
                'total_devolucion' => $td !== $ct['total_devolucion'] ? 'danger':'', 
                'total_pagos' => $tp !== $ct['total_pagos'] ? 'danger':'', 
                'total_pagar' => $tr !== $ct['total_pagar'] ? 'danger':''
            ],
            '_showDetails' => true,
            'by_cortes' => $lista
        ];
    }

    // REGISTRAR PAGO POR CORTE Y CLIENTE
    public function h_registrar_pago($cliente_id, $corte_id){
        $ci = $corte_id;
        if($corte_id == 0){
            $hoy = Carbon::now();
            $corte = Corte::where('inicio', '<', $hoy)
                            ->where('final', '>', $hoy)
                            ->first();  
            $ci = $corte->id;
        }

        $cctotale = Cctotale::where('cliente_id', $cliente_id)
                                ->where('corte_id', $ci)
                                ->with('cliente', 'corte')
                                ->first();
        $remdepositos = Remdeposito::where('remcliente_id', $cctotale->cliente->remcliente->id)
                                ->where('corte_id', $ci)
                                ->orderBy('fecha', 'desc')->get();  
        return view('information.historial.add-pago-corte', compact('cctotale','remdepositos'));
    }
}
