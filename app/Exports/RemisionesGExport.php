<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Remisione;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class RemisionesGExport implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct($cliente_id, $inicio, $final, $estado)
    {
        $this->cliente_id = $cliente_id;
        $this->inicio = $inicio;
        $this->final = $final;
        $this->estado = $estado;
    }

    public function view(): View
    {
        $remisiones = $this->get_remisiones();
        $valores = $this->totales($remisiones);
        return view('download.excel.remisiones.reporte-remisiones-gral', [
            'fecha' => $valores['fecha'],
            'inicio' => $this->inicio,
            'final' => $this->final,
            'estado' => $this->estado,
            'cliente_id' => $this->cliente_id,
            'remisiones' => $remisiones,
            'totales' => $valores['totales']
        ]);
    }

    public function get_todo($inicio, $final){
        $datos = [];
        if($this->cliente_id === 'null' && $this->inicio === '0000-00-00' && $this->final === '0000-00-00' && $this->estado === 'null'){
            $datos = \DB::table('remisiones')
                ->whereNotIn('remisiones.estado', ['Iniciado', 'Cancelado'])
                ->join('clientes', 'remisiones.cliente_id', '=', 'clientes.id')
                ->select('clientes.name as nombre', 
                    \DB::raw('SUM(total) as total'),
                    \DB::raw('SUM(pagos) as pagos'),
                    \DB::raw('SUM(total_devolucion) as total_devolucion'),
                    \DB::raw('SUM(total_pagar) as total_pagar')
                )->groupBy('clientes.name', 'clientes.id')
                ->orderBy('clientes.id','asc')
                ->get();
        }
        if($this->cliente_id === 'null' && $this->inicio != '0000-00-00' && $this->final != '0000-00-00' && $this->estado === 'null'){
            $datos = \DB::table('remisiones')
                ->whereBetween('fecha_creacion', [$inicio, $final])
                ->whereNotIn('remisiones.estado', ['Iniciado', 'Cancelado'])
                ->join('clientes', 'remisiones.cliente_id', '=', 'clientes.id')
                ->select('clientes.name as nombre', 
                    \DB::raw('SUM(total) as total'),
                    \DB::raw('SUM(pagos) as pagos'),
                    \DB::raw('SUM(total_devolucion) as total_devolucion'),
                    \DB::raw('SUM(total_pagar) as total_pagar')
                )->groupBy('clientes.name', 'clientes.id')
                ->orderBy('clientes.id','asc')
                ->get();
        }
        
        return $datos;
    }

    public function get_remisiones(){
        if($this->cliente_id === 'null' && $this->inicio === '0000-00-00' && $this->final === '0000-00-00' && $this->estado === 'null'){
            $remisiones = Remisione::with(['cliente:id,name'])
                    ->orderBy('id','desc')
                    ->get();
        }
        if($this->cliente_id !== 'null' && $this->inicio === '0000-00-00' && $this->final === '0000-00-00' && $this->estado === 'null'){
            $remisiones = Remisione::where('cliente_id', $this->cliente_id)
                    ->orderBy('id','desc')
                    ->get();
        }
        if($this->cliente_id !== 'null' && $this->inicio != '0000-00-00' && $this->final != '0000-00-00' && $this->estado === 'null'){
            $remisiones = Remisione::where('cliente_id', $this->cliente_id)
                            ->whereBetween('fecha_creacion', [$this->inicio, $this->final])
                            ->orderBy('id','desc')
                            ->get();
        }
        if($this->cliente_id === 'null' && $this->inicio != '0000-00-00' && $this->final != '0000-00-00' && $this->estado === 'null'){
            $remisiones = Remisione::whereBetween('fecha_creacion', [$this->inicio, $this->final])
                            ->orderBy('id','desc')
                            ->with('cliente')->get();
        }
        if($this->estado !== 'null'){
            $remisiones = $this->get_por_estado();
        }
        
        return $remisiones;
    }

    public function get_por_estado(){
        if($this->estado === 'cancelado'){
            if($this->cliente_id === 'null'){ $remisiones = $this->estadoS_SF(4, null); }
            else { $remisiones = $this->estadoS_SF(4, $this->cliente_id); } 
        }
        if($this->estado === 'no_entregado'){
            if($this->cliente_id === 'null'){ $remisiones = $this->estadoS_SF(1, null); }
            else { $remisiones = $this->estadoS_SF(1, $this->cliente_id); }
        }
        if($this->estado === 'entregado'){
            if($this->cliente_id === 'null'){ $remisiones = $this->estadoS_SF(2, null); }
            else { $remisiones = $this->estadoS_SF(2, $this->cliente_id); }
        }
        if($this->estado === 'pagado'){
            if($this->cliente_id === 'null'){ $remisiones = $this->pagado_SF(null);  }
            else { $remisiones = $this->pagado_SF($this->cliente_id);  }
        }
        return $remisiones;
    }

    // FUNCIÓN PARA LA BUSQUEDA DE REMISIÓN POR ESTADO SIN FECHA
    public function estadoS_SF($estado, $cliente_id){
        if ($estado == 1 || $estado == 4) {
            // REMISIONES INICIADAS Y CANCELADAS
            if($cliente_id === null){
                return Remisione::where('estado',$estado)
                            ->orderBy('id','desc')
                            ->with('cliente:id,name')
                            ->get();
            } else {
                return Remisione::where('estado',$estado)
                            ->where('cliente_id', $cliente_id)
                            ->orderBy('id','desc')
                            ->with('cliente:id,name')
                            ->get();
            }
        }
        if ($estado == 2){
            // REMISIONES EN PROCESO
            if($cliente_id === null){
                return Remisione::where('total_pagar', '>', 0)
                    ->where('estado', 'Proceso')
                    ->orderBy('id','desc')
                    ->with('cliente:id,name')
                    ->get();
            }
            else {
                return Remisione::where('cliente_id', $cliente_id)
                    ->where('total_pagar', '>', 0)
                    ->where('estado', 'Proceso')
                    ->orderBy('id','desc')
                    ->with('cliente:id,name')
                    ->get();
            }
        }
    }

    // FUNCIÓN PARA LA BUSQUEDA DE REMISIÓN POR ESTADO (PAGADO) SIN FECHA
    public function pagado_SF($cliente_id){
        if($cliente_id === null){
            return Remisione::where('total_pagar', '=', 0)
                        ->where(function ($query) {
                            $query->where('pagos', '>', 0)
                                    ->orWhere('total_devolucion', '>', 0);
                        })
                        ->orderBy('id','desc')
                        ->with('cliente:id,name')
                        ->get();
        }
        else {
            return Remisione::where('cliente_id', $cliente_id)
                        ->where('total_pagar', '=', 0)
                        ->where(function ($query) {
                            $query->where('pagos', '>', 0)
                                    ->orWhere('total_devolucion', '>', 0);
                        })
                        ->orderBy('id','desc')
                        ->with('cliente:id,name')
                        ->get();
        }
    }

    public function totales($remisiones){
        $total_salida = 0;
        $total_pagos = 0;
        $total_devolucion = 0;
        $total_pagar = 0;

        $remisiones->map(function($r) use (&$total_salida, &$total_pagos, &$total_devolucion, &$total_pagar){
            if($r->estado === 'Proceso' || $r->estado === 'Terminado'){
                $total_salida += $r->total;
                $total_pagos += $r->pagos;
                $total_devolucion += $r->total_devolucion;
                $total_pagar += $r->total_pagar;    
            } 
        });
        $datos = [
            'fecha' => Carbon::now(),
            'totales' => [
                'total_salida' => $total_salida,
                'total_pagos' => $total_pagos,
                'total_devolucion' => $total_devolucion,
                'total_pagar' => $total_pagar
            ]
        ];
        return $datos;
    }
}
