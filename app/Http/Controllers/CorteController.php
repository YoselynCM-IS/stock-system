<?php

namespace App\Http\Controllers;

use Intervention\Image\ImageManagerStatic as Image;
use Spatie\Dropbox\Client as ClienteDropbox;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Remdeposito;
use App\Remcliente;
use App\Editoriale;
use Carbon\Carbon;
use App\Remisione;
use App\Deposito;
use App\Cctotale;
use App\Ectotale;
use App\Reporte;
use App\Cliente;
use App\Adeudo;
use App\Abono;
use App\Corte;
use App\Foto;

class CorteController extends Controller
{
    // Obtener todos los cortes (PAGINADO)
    public function index() {
        $c = collect();
        $cortes = Corte::orderBy('inicio', 'desc')->get();
        $cortes->map(function($corte) use (&$c){
            $cctotales = Cctotale::where('corte_id', $corte->id);
            $c->push([
                'id' => $corte->id,
                'tipo' => $corte->tipo, 
                'inicio' => $corte->inicio, 
                'final' => $corte->final,
                'total' => $cctotales->sum('total'), 
                'total_devolucion' => $cctotales->sum('total_devolucion'), 
                'total_pagos' => $cctotales->sum('total_pagos'),
                'total_pagar' => $cctotales->sum('total_pagar')
            ]);
        });
        return response()->json($c);
    }

    // OBTENER TODOS LOS CORTES
    public function get_all(){
        $cortes = Corte::orderBy('inicio', 'desc')->get();
        return response()->json($cortes);
    }

    // OBTENER CORTES DEL CLIENTE
    public function list_bycliente(Request $request){
        $cctotales = Cctotale::where('cliente_id', $request->cliente_id)
                        ->select('corte_id')->get();
        $ids = [];
        $cctotales->map(function($cctotale) use(&$ids){
            $ids[] = $cctotale->corte_id;
        });
        $cortes = Corte::whereIn('id', $ids)->orderBy('inicio', 'desc')
                        ->get();
        return response()->json($cortes);
    }

    // Obtener detalles de un corte
    public function show(Request $request){
        $cctotales = Cctotale::where('corte_id', $request->corte_id)
                                ->where('total', '>', 0)
                                ->with('cliente', 'corte')->get();
        
        $clientes = collect($this->org_remisiones($cctotales));
        return response()->json($clientes);
    }

    // Obtener un corte
    public function show_one(Request $request){
        $cctotale = $this->get_cctotale($request->corte_id, $request->cliente_id);
        return response()->json($cctotale);
    }

    // ORGANIZAR REMISIONES POR CLIENTE
    public function org_remisiones($cctotales){
        $clientes = [];
        $cctotales->map(function($cctotale) use(&$clientes){
            $remcliente = Remcliente::where('cliente_id', $cctotale->cliente_id)->first();
            $remdepositos = Remdeposito::where('remcliente_id', $remcliente->id)
                                ->where('corte_id', $cctotale->corte_id)
                                ->whereNotIn('tipo', ['adeudo'])
                                ->with('foto')->orderBy('created_at', 'desc')->get();
            $remisiones = Remisione::where('corte_id', $cctotale->corte_id)
                            ->where('cliente_id', $cctotale->cliente_id)
                            ->where(function ($query) {
                                $query->where('estado', '=', 'Proceso')
                                    ->orWhere('estado', '=', 'Terminado');
                            })->orderBy('id', 'desc')->get();
            
            $corte_title = null;
            if($cctotale->total_favor > 0) {
                $cf = Corte::find($cctotale->corte_id_favor);
                $corte_title = $cf->tipo.': '.$cf->inicio.'/'.$cf->final;
            }
            $datos = [
                'visible' => false,
                'corte_id' => $cctotale->corte_id,
                'corte' => $cctotale->tipo,
                'inicio' => $cctotale->inicio,
                'final' => $cctotale->final,
                'cliente_id' => $cctotale->cliente_id,
                'cliente' => $cctotale->cliente,
                'total' => $cctotale->total, 
                'total_devolucion' => $cctotale->total_devolucion, 
                'total_pagos' => $cctotale->total_pagos,
                'total_pagar' => $cctotale->total_pagar,
                'total_favor' => $cctotale->total_favor,
                'corte_id_favor' => $corte_title,
                'remisiones' => $remisiones,
                'remdepositos' => $remdepositos
            ];
            $clientes[] = $datos;
        });
        return $clientes;
    }

    // Obtener detalles de un corte de un cliente
    public function show_bycliente(Request $request){
        $cctotales = Cctotale::where('corte_id', $request->corte_id)
                                ->where('cliente_id', $request->cliente_id)
                                ->with('cliente', 'corte')->get();
        $clientes = collect($this->org_remisiones($cctotales));
        return response()->json($clientes);
    }

    // Crear corte
    public function store(Request $request){
        \DB::beginTransaction();
        try {
            $corte = Corte::create($request->all());

            $remclientes = Remcliente::orderBy('cliente_id', 'asc')
                            ->where('total', '>', 0)->get();
            $remclientes->map(function($remcliente) use($corte){
                Cctotale::create([
                    'corte_id' => $corte->id, 
                    'cliente_id' => $remcliente->cliente_id
                ]);
            });

            $editoriales = Editoriale::get();
            $editoriales->map(function($editorial) use($corte){
                Ectotale::create([
                    'corte_id' => $corte->id, 
                    'editoriale_id' => $editorial->id
                ]);
            });
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json();
    }

    // Actualizar corte
    public function update(Request $request){
        $corte = Corte::find($request->id);
        \DB::beginTransaction();
        try {
            $corte->update([
                'tipo' => $request->tipo,
                'inicio' => $request->inicio,
                'final' => $request->final
            ]);
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json($corte);
    }

    // OBTENER REMISIONES DISPONIBLES
    public function get_remisiones(){
        $remisiones = Remisione::where('corte_id', 0)
                        ->where(function ($query) {
                            $query->where('estado', '=', 'Proceso')
                                ->orWhere('estado', '=', 'Terminado');
                        })->with('cliente')
                        ->orderBy('id', 'desc')->paginate(20);
        return response()->json($remisiones);
    }

    // OBTENER REMISIONES DISPONIBLES POR CLIENTE
    public function rems_bycliente(Request $request){
        $remisiones = Remisione::where('cliente_id', $request->cliente_id)
                            ->where('corte_id', 0)
                            ->where(function ($query) {
                                $query->where('estado', '=', 'Proceso')
                                    ->orWhere('estado', '=', 'Terminado');
                            })->with('cliente')
                            ->orderBy('id', 'desc')->paginate(20);
        return response()->json($remisiones);
    }

    // GUARDAR CLASIFICACIÓN DE REMISIONES
    public function clasificar_rems(Request $request){
        $remisiones = collect($request->remisiones);
        $corte_id = (int)$request->corte_id;
        $cliente_id = (int)$request->cliente_id;

        $totales = [
            'total' => 0, 
            'total_devolucion' => 0, 
            'total_pagos' => 0,
            'total_pagar' => 0
        ];

        \DB::beginTransaction();
        try {
            $remisiones->map(function($remision) use(&$totales, $corte_id){
                $remision = Remisione::find($remision['id']);
                $depositos = Deposito::where('remisione_id', $remision->id)->get();
        
                $total_pagos = 0;
                $total_pagar = $remision->total;
                $total_devolucion = $remision->total_devolucion;
 
                if($depositos->count() > 0) $total_pagos = $depositos->sum('pago');
                if($depositos->count() > 0 || $total_devolucion > 0)
                    $total_pagar = $remision->total - ($total_pagos + $total_devolucion);

                $totales['total'] += $remision->total;
                $totales['total_devolucion'] += $total_devolucion;
                $totales['total_pagos'] += $total_pagos;
                $totales['total_pagar'] += $total_pagar;
                $remision->update([
                    'corte_id' => $corte_id
                ]);
            });
    
            $cctotale = $this->get_cctotale($corte_id, $cliente_id);

            $cctotale->update([
                'total' => $cctotale->total + $totales['total'], 
                'total_devolucion' => $cctotale->total_devolucion + $totales['total_devolucion'], 
                'total_pagos' => $cctotale->total_pagos + $totales['total_pagos'],
                'total_pagar' => $cctotale->total_pagar + $totales['total_pagar']
            ]);
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }

        return response()->json();
    }

    // Obtener cctotale
    public function get_cctotale($corte_id, $cliente_id){
        return Cctotale::where([
            'corte_id' => $corte_id,
            'cliente_id' => $cliente_id
        ])->first();
    }

    // OBTENER PAGOS GRALS DISPONIBLES
    public function get_pagos(){
        $remdepositos = Remdeposito::where('corte_id', 0)
                        ->with('remcliente.cliente')
                        ->orderBy('created_at', 'desc')->paginate(20);
        return response()->json($remdepositos);
    }

    // OBTENER PAGOS DE UN CLIENTE
    public function pagos_bycliente(Request $request){
        $remcliente = Remcliente::where('cliente_id', $request->cliente_id)->first();
        if($remcliente != null){
            $remdepositos = Remdeposito::where('corte_id', 0)
                        ->where('remcliente_id', $remcliente->id)
                        ->with('remcliente.cliente')
                        ->orderBy('created_at', 'desc')->paginate(20);
            return response()->json($remdepositos);
        }
        return response()->json(false);
    }

    // GUARDAR PAGOS SELECCIONADOS
    public function clasificar_pagos(Request $request){
        $pagos = collect($request->pagos);
        $corte_id = (int)$request->corte_id;
        $cliente_id = (int)$request->cliente_id;
        $corte_id_favor = (int)$request->corte_id_favor;
        $total_pagos = 0;

        \DB::beginTransaction();
        try {
            $pagos->map(function($pago) use(&$total_pagos, $corte_id){
                $remdeposito = Remdeposito::find($pago['id']);
                $total_pagos += $remdeposito->pago;
                $remdeposito->update([
                    'corte_id' => $corte_id
                ]);
            });

            $this->validate_favor($corte_id, $cliente_id, $corte_id_favor, $total_pagos);
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }

        return response()->json(1);
    }

    // VALIDAR CORTE A FAVOR
    public function validate_favor($corte_id, $cliente_id, $corte_id_favor, $total_pagos){
        $cctotale = $this->get_cctotale($corte_id, $cliente_id);
        if($corte_id_favor == null) {
            $this->update_cctotale($cctotale, $total_pagos);
        } else {
            $total_favor = $total_pagos - $cctotale->total_pagar;
            $cctotale->update([
                'corte_id_favor' => $corte_id_favor,
                'total_favor' => $cctotale->total_favor + $total_favor,
                'total_pagos' => $cctotale->total_pagos + $cctotale->total_pagar,
                'total_pagar' => 0
            ]);

            $cctotale_favor = $this->get_cctotale($corte_id_favor, $cliente_id);
            $this->update_cctotale($cctotale_favor, $total_favor);
        }
    }

    // Actualizar cctotale
    public function update_cctotale($cctotale, $total_pagos){
        $cctotale->update([
            'total_pagos' => $cctotale->total_pagos + $total_pagos,
            'total_pagar' => $cctotale->total_pagar - $total_pagos
        ]);
    }

    // verificar si es mayor o menor
    public function verify_totales(Request $request){
        $cctotale = $this->get_cctotale($request->corte_id, $request->cliente_id);
        if($cctotale->total_pagar == 0) return response()->json(2);
        if($cctotale->total_pagar > 0 && ((double)$request->total_selected > $cctotale->total_pagar)) return response()->json(3);
        
        return response()->json(1);
    }

    // MANDAR A LA VISTA DE DETALLES DE CORTES DEL CLIENTE
    public function details_cliente($cliente_id){
        return view('information.cortes.clientes.details-cliente', compact('cliente_id'));
    } 

    // OBTENER CORTES DEL CLIENTE
    public function by_cliente(Request $request){
        $cliente_id = $request->cliente_id;
        $remcliente = Remcliente::where('cliente_id', $cliente_id)
                            ->with('cliente')->first();
        $cctotales = $this->get_cctotales_cliente($cliente_id);
        $cortes = $this->org_remisiones($cctotales);
        // ACTUALIZAR SALDOS
        $hoy = Carbon::now();
        $saldos = Adeudo::where('cliente_id', $cliente_id)->where('saldo_pendiente', '>', 0)->get();
        $saldos->map(function($adeudo) use($hoy){
            $diferencia = $adeudo->created_at->diffInDays($hoy);
            $rango = $this->get_rango($diferencia);
            $adeudo->update(['dias' => $diferencia, 'rango' => $rango]);
        });
        // FIN ACTUALIZAR SALDOS
        $adeudos = Adeudo::where('cliente_id', $cliente_id)->with('corte')->orderBy('created_at', 'desc')->get();
        $data = [
            'cliente_id' => $cliente_id,
            'name'  => $remcliente->cliente->name,
            'moneda' => $remcliente->cliente->moneda,
            'total' => $remcliente->total,
            'total_pagos' => $remcliente->total_pagos,
            'total_devolucion' => $remcliente->total_devolucion,
            'total_pagar' => $remcliente->total_pagar,
            'cortes' => $cortes,
            'adeudos' => $adeudos
        ];    
        return response()->json($data);
    }

    // OBTENER LOS CORTES
    public function get_cctotales_cliente($cliente_id){
        return \DB::table('cctotales')
            ->select('cctotales.*', 'cortes.tipo', 'cortes.inicio',
                    'cortes.final', 'clientes.name as cliente'
            )->where('cliente_id', $cliente_id)
            ->join('cortes', 'cctotales.corte_id', '=', 'cortes.id')
            ->join('clientes', 'cctotales.cliente_id', '=', 'clientes.id')
            ->orderBy('cortes.inicio', 'desc')
            ->get();
    }

    // OBTENER RANGO
    public function get_rango($dias){
        // ['0-29', '30-59', '60-89', '90-119', '120-149', '+150']
        if($dias >= 0 && $dias <= 29) return 1;
        if($dias >= 30 && $dias <= 59) return 2;
        if($dias >= 60 && $dias <= 89) return 3;
        if($dias >= 90 && $dias <= 119) return 4;
        if($dias >= 120 && $dias <= 149) return 5;
        if($dias >= 150) return 6;
    }

    // PAGO AL CORTE
    public function save_payment(Request $request){
        $corte_id = (int)$request->corte_id;
        $cliente_id = (int)$request->cliente_id;
        $corte_id_favor = (int)$request->corte_id_favor;
        
        $remcliente = Remcliente::where('cliente_id', $cliente_id)->first();
        
        try{
            \DB::beginTransaction();

            $monto = (float) $request->pago;
            $remdeposito = $this->save_remdeposito($remcliente->id, $corte_id, $monto, $request->fecha, $request->nota, $request->tipo);
            
            $this->validate_favor($corte_id, $cliente_id, $corte_id_favor, $monto);

            $total_pagar = $remcliente->total_pagar - $monto;
            $remcliente->update([
                'total_pagos' => $remcliente->total_pagos + $monto, 
                'total_pagar' => $total_pagar
            ]);

            if((float) $total_pagar <= 0){
                $this->cerrar_remisiones($cliente_id, $corte_id);
            }

            $reporte = 'registro un pago del cliente '.$remcliente->cliente->name.' PAGO: '.$remdeposito->fecha.' / $'.$remdeposito->pago.' / '.$remdeposito->nota;
            $this->create_report($remdeposito->id, $reporte, 'remdepositos');
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json();
    }

    public function save_remdeposito($remcliente_id, $corte_id, $pago, $fecha, $nota, $tipo){
        return Remdeposito::create([
            'remcliente_id' => $remcliente_id,
            'corte_id' => $corte_id,
            'pago' => $pago,
            'fecha' => $fecha,
            'nota' => $nota,
            'tipo' => $tipo,
            'ingresado_por' => auth()->user()->name
        ]);
    }

    // HISTORIAL
    // Guardar pago para historial
    public function h_save_payment(Request $request){
        $corte_id = (int)$request->corte_id;
        $cliente_id = (int)$request->cliente_id;
        $corte_id_favor = (int)$request->corte_id_favor;
        
        $cctotale = Cctotale::where('cliente_id', $cliente_id)
                            ->where('corte_id', $corte_id)
                            ->first();
        try{
            \DB::beginTransaction();

            $monto = (float) $request->pago;
            
            Remdeposito::create([
                'remcliente_id' => $cctotale->cliente->remcliente->id,
                'corte_id' => $corte_id,
                'pago' => $monto,
                'fecha' => $request->fecha,
                'nota' => $request->nota,
                'ingresado_por' => auth()->user()->name
            ]);

            $this->validate_favor($corte_id, $cliente_id, $corte_id_favor, $monto);

            $total_pagar = $cctotale->total_pagar - $monto;

            if((float) $total_pagar <= 0){
                $this->cerrar_remisiones($cliente_id, $corte_id);
            }
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json();
    }

    // CERRAR REMISIONES
    public function cerrar_remisiones($cliente_id, $corte_id){
        $remisiones = Remisione::where([
            'cliente_id' => $cliente_id, 
            'corte_id' => $corte_id,
            'estado' => 'Proceso'
        ])->get();

        $remisiones->map(function($remision){
            $remision->update([
                'pagos' => $remision->pagos + $remision->total_pagar,
                'total_pagar'   => 0,
                'estado' => 'Terminado'
            ]);
        });
    }

    // MOVER REMISION
    public function move_rem(Request $request){
        $remision = Remisione::find($request->remisione_id);
        $cliente_id = $remision->cliente_id;
        $corte_id = $request->corte_id;

        if($corte_id != $remision->corte_id){
            $total = $remision->total;
            $total_pagar = $remision->total_pagar;

            $anterior = $this->get_cctotale($remision->corte_id, $cliente_id);
            $anterior->update([
                'total' => $anterior->total - $total,
                'total_pagar' => $anterior->total_pagar - $total_pagar
            ]);

            $nuevo = $this->get_cctotale($corte_id, $cliente_id);
            $nuevo->update([
                'total' => $nuevo->total + $total,
                'total_pagar' => $nuevo->total_pagar + $total_pagar
            ]);

            $remision->update([ 'corte_id' => $corte_id ]);

            $corte_anterior = 'Temporada '.$anterior->corte->tipo.' '.$anterior->corte->inicio.' - '.$anterior->corte->final;
            $corte_nuevo = 'Temporada '.$nuevo->corte->tipo.' '.$nuevo->corte->inicio.' - '.$nuevo->corte->final;
            $reporte = 'movió la remisión '.$corte_anterior.': '.$remision->id.' / '.$remision->cliente->name.' a '.$corte_nuevo;
            $this->create_report($nuevo->id, $reporte, 'cctotales');            

            return response()->json(true);
        }
        return response()->json(false);
    }

    // MOVER PAGO
    public function move_pago(Request $request){
        $remdeposito = Remdeposito::find($request->pago_id);
        $corte_anterior = 'Temporada '.$remdeposito->corte->tipo.' '.$remdeposito->corte->inicio.' - '.$remdeposito->corte->final;
        $pago = $remdeposito->pago;
        $corte_id = (int)$request->corte_id;
        $cliente_id = (int)$request->cliente_id;
        $corte_id_favor = (int)$request->corte_id_favor;

        if($corte_id != $remdeposito->corte_id){
            $anterior = $this->get_cctotale($remdeposito->corte_id, $cliente_id);
            $anterior->update([
                'total_pagos' => $anterior->total_pagos - $pago,
                'total_pagar' => $anterior->total_pagar + $pago
            ]);

            $this->validate_favor($corte_id, $cliente_id, $corte_id_favor, $pago);
            $remdeposito->update([ 'corte_id' => $corte_id ]);

            
            $corte_nuevo = 'Temporada '.$remdeposito->corte->tipo.' '.$remdeposito->corte->inicio.' - '.$remdeposito->corte->final;
            $reporte = 'movió el pago del cliente '.$remdeposito->remcliente->cliente->name.': '.$corte_anterior.' -- '.$remdeposito->fecha.' / $'.$remdeposito->pago.' / '.$remdeposito->nota.' a '.$corte_nuevo;
            $this->create_report($remdeposito->id, $reporte, 'remdepositos');

            return response()->json(true);
        }
        return response()->json(false);
    }

    // EDITAR PAGO
    public function edit_payment(Request $request){
        $remdeposito = Remdeposito::find($request->id);
        $remcliente = Remcliente::find($remdeposito->remcliente_id);

        $pago_anterior = $remdeposito->fecha.' / $'.$remdeposito->pago.' / '.$remdeposito->nota;
        $pago = (float) $request->pago;
        $cliente_id = $remcliente->cliente_id;
        $corte_id = $remdeposito->corte_id;

        $cctotale = $this->get_cctotale($corte_id, $cliente_id);
        try{
            \DB::beginTransaction();
            // CCTOTALE
            $c_total_pagos = ($cctotale->total_pagos - $remdeposito->pago) + $pago;
            $c_total_pagar = ($cctotale->total_pagar + $remdeposito->pago) - $pago;
            $cctotale->update([
                'total_pagos' => $c_total_pagos,
                'total_pagar' => $c_total_pagar
            ]);
            // REMCLIENTE
            $r_total_pagos = ($remcliente->total_pagos - $remdeposito->pago) + $pago;
            $r_total_pagar = ($remcliente->total_pagar + $remdeposito->pago) - $pago;
            $remcliente->update([
                'total_pagos' => $r_total_pagos,
                'total_pagar' => $r_total_pagar
            ]);

            // REMDEPOSITO
            $remdeposito->update([
                'pago' => $pago,
                'fecha' => $request->fecha,
                'nota' => $request->nota,
            ]);

            // CERRAR REMISIONES
            if((float) $cctotale->total_pagar == 0){
                $this->cerrar_remisiones($cliente_id, $corte_id);
            }

            $pago_nuevo = $remdeposito->fecha.' / $'.$remdeposito->pago.' / '.$remdeposito->nota;
            $reporte = 'edito el pago del cliente '.$remcliente->cliente->name.': '.$pago_anterior.' a '.$pago_nuevo;
            $this->create_report($remdeposito->id, $reporte, 'remdepositos');
        \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json($remdeposito);
    }

    // ELIMINAR PAGO
    public function delete_payment(Request $request){
        $remdeposito = Remdeposito::find($request->pago_id);
        $remcliente = Remcliente::find($remdeposito->remcliente_id);

        $cliente_id = $remcliente->cliente_id;
        $corte_id = $remdeposito->corte_id;

        $cctotale = $this->get_cctotale($corte_id, $cliente_id);
        try{
            \DB::beginTransaction();
            // CCTOTALE
            $cctotale->update([
                'total_pagos' => $cctotale->total_pagos - $remdeposito->pago,
                'total_pagar' => $cctotale->total_pagar + $remdeposito->pago
            ]);
            // REMCLIENTE
            $remcliente->update([
                'total_pagos' => $remcliente->total_pagos - $remdeposito->pago,
                'total_pagar' => $remcliente->total_pagar + $remdeposito->pago
            ]);

            $reporte = 'elimino un pago del cliente '.$remcliente->cliente->name.' PAGO: '.$remdeposito->fecha.' / $'.$remdeposito->pago.' / '.$remdeposito->nota;
            $this->create_report($remdeposito->id, $reporte, 'remdepositos');

            // REMDEPOSITO
            $remdeposito->delete();
        \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json(true);
    }

    public function upload_payment(Request $request){
        // VALIDACIÓN DE DATOS
        $this->validate($request, [
            'file' => ['required', 'mimes:jpg,png,jpeg', 'max:3072']
        ]);

        \DB::beginTransaction();
        try {
            $remdeposito = Remdeposito::find($request->pagoid);
            // SUBIR IMAGEN
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            $name_file = "id-".$remdeposito->id."_".time().".".$extension;
            
            $ruta = str_replace(' ', '-', env('APP_NAME')).'/clientes/pagos/';
            Storage::disk('dropbox')->putFileAs($ruta, $request->file('file'), $name_file);
            $client = new ClienteDropbox(env('DROPBOX_TOKEN'));
            $response = $client->createSharedLinkWithSettings(
                $ruta.$name_file, ["requested_visibility" => "public"]
            );
            $public_url = $response['url'];

            // $image = Image::make($request->file('file'));
            // $image->resize(1280, null, function ($constraint) {
            //     $constraint->aspectRatio();
            //     $constraint->upsize();
            // });
    
            // Storage::disk('dropbox')->put(
            //     '/stock1/'.$name_file, (string) $image->encode('jpg', 30)
            // );

            $foto = Foto::create([
                'remdeposito_id' => $remdeposito->id,
                'name' => $name_file,
                'extension' => $extension,
                'public_url' => $public_url,
                // 'size' => $response['size'],
                'creado_por' => auth()->user()->name
            ]);

            $reporte = 'subió un comprobante de pago del cliente '.$remdeposito->remcliente->cliente->name.' PAGO: '.$remdeposito->fecha.' / $'.$remdeposito->pago.' / '.$remdeposito->nota;
            $this->create_report($remdeposito->id, $reporte, 'remdepositos');
            \DB::commit();
        }  catch (Exception $e) {
            \DB::rollBack();
        }
        
        return response()->json($foto);
    }

    // OBTENER URL DE LA FOTO
    public function image_url(Request $request){
        $remdeposito = Remdeposito::find($request->pago_id);
        $public_url = $remdeposito->foto->public_url;
        if($public_url == NULL){
            $ruta = str_replace(' ', '-', env('APP_NAME')).'/clientes/pagos/'.$remdeposito->foto->name;
            $url = Storage::disk('dropbox')->url($ruta);
        } else {
            $url = str_replace('www.dropbox.com', 'dl.dropboxusercontent.com', $public_url);
        }

        return response()->json($url);
    }

    public function create_report($remdeposito_id, $reporte, $tabla){
        Reporte::create([
            'user_id' => auth()->user()->id, 
            'type' => 'cliente', 
            'reporte' => $reporte,
            'name_table' => $tabla, 
            'id_table' => $remdeposito_id
        ]);
    }

    public function by_ficticios(Request $request){
        $cliente = Cliente::find($request->cliente_id);
        $remdepositos = Remdeposito::where('remcliente_id', $cliente->remcliente->id)
                            ->where('tipo', 'ficticio')
                            ->with('corte')
                            ->orderBy('created_at', 'desc')->get();

        if($request->statusCurrency == 'true'){
            $valor = $cliente->moneda->valor;
            $curr_depositos = collect();
            $remdepositos->map(function($rd) use(&$curr_depositos, $valor){
                $curr_depositos->push($this->set_remdeposito($rd, $valor));
            });
            return response()->json([
                'remdepositos' => $curr_depositos,
                'total' => $curr_depositos->sum('pago')
            ]);
        }

        return response()->json([
            'remdepositos' => $remdepositos,
            'total' => $remdepositos->sum('pago')
        ]);
    }

    // GUARDAR ADEUDO
    public function save_adeudo(Request $request){
        \DB::beginTransaction();
        try {
            $cliente_id = (int)$request->cliente_id;
            $remcliente = Remcliente::where('cliente_id', $cliente_id)->first();
            $corte_id = (int)$request->corte_id;
            $total_pagar = (double) $request->total_pagar;
            $hoy = Carbon::now();
            $nota = $hoy->format('Y-m-d H:i:s').' SALDO DEUDOR';

            $remdeposito = $this->save_remdeposito($remcliente->id, $corte_id, $total_pagar, $hoy->format('Y-m-d'), $nota, 'adeudo');
            $cctotale = $this->get_cctotale($corte_id, $cliente_id);
            $this->update_cctotale($cctotale, $total_pagar);
            $remcliente->update([
                'total_pagos' => $remcliente->total_pagos + $total_pagar, 
                'total_pagar' => $remcliente->total_pagar - $total_pagar
            ]);

            $adeudo = Adeudo::create([
                'cliente_id' => $cliente_id, 
                'corte_id' => $corte_id, 
                'remdeposito_id' => $remdeposito->id,
                'saldo_inicial' => $total_pagar, 
                'saldo_pendiente' => $total_pagar,  
                'ingresado_por' => auth()->user()->name
            ]);
            \DB::commit();
        }  catch (Exception $e) {
            \DB::rollBack();
        }
        return response()->json($remcliente);
    }

    // GUARDAR ABONO
    public function save_abono(Request $request){
        $this->validate($request, [
            'pago' => 'required|numeric|min:0.1',
            'fecha' => 'required|date',
            'nota' => 'required|min:5'
        ]);

        $adeudo_id = (int) $request->adeudo_id;
        $fecha = $request->fecha;
        $pago = (double) $request->pago;
        $nota = $request->nota;
        $adeudo = Adeudo::find($adeudo_id);

        if($pago <= $adeudo->saldo_pendiente){
            \DB::beginTransaction();
            try {
                Abono::create([
                    'adeudo_id' => $adeudo_id, 
                    'fecha' => $fecha,
                    'pago' => $pago, 
                    'nota' => $nota, 
                    'ingresado_por' => auth()->user()->name
                ]);

                $adeudo->update([
                    'saldo_pagado' => $adeudo->saldo_pagado + $pago,
                    'saldo_pendiente' => $adeudo->saldo_pendiente - $pago
                ]);
                
                $hoy = Carbon::now();
                $saldo_deudor = Remdeposito::find($adeudo->remdeposito_id);
                $saldo_deudor->update([
                    'pago' => $saldo_deudor->pago - $pago,
                    'nota' => $saldo_deudor->nota.' / '.$hoy->format('Y-m-d H:i:s').' abono $'.$pago
                ]);
                $remdeposito = $this->save_remdeposito($saldo_deudor->remcliente_id, $adeudo->corte_id, $pago, $fecha, $nota, 'real');
                \DB::commit();
            }  catch (Exception $e) {
                \DB::rollBack();
            }
            return response()->json(['status' => true, 'message' => 'El pago se guardó correctamente.']);
        }

        return response()->json(['status' => false, 'message' => 'El pago tiene que ser menor o igual al saldo pendiente.']);
    }

    // OBTENER ABONOS
    public function get_abonos(Request $request){
        $abonos = Abono::where('adeudo_id', $request->adeudo_id)->orderBy('created_at', 'desc')->get();
        if($request->statusCurrency == 'true'){
            $adeudo = Adeudo::find($request->adeudo_id);
            $valor = $adeudo->cliente->moneda->valor;
            $curr_abonos = collect();
            $abonos->map(function($abono) use(&$curr_abonos, $valor){
                $curr_abonos->push([
                    'adeudo_id' => $abono->adeudo_id,
                    'created_at' => $abono->created_at,
                    'deleted_at' => $abono->deleted_at,
                    'fecha' => $abono->fecha,
                    'id' => $abono->id,
                    'ingresado_por' => $abono->ingresado_por,
                    'nota' => $abono->nota,
                    'pago' => $abono->pago * $valor,
                    'updated_at' => $abono->updated_at,
                ]);
            });

            return response()->json($curr_abonos);
        }
        
        return response()->json($abonos);
    }

    // CAMBIAR EL TIPO DE MONEDA DE LOS DETALLES DEL CORTE
    public function chance_currency(Request $request){
        $cliente_id = $request->cliente_id;
        $remcliente = Remcliente::where('cliente_id', $cliente_id)
                            ->with('cliente')->first();
        $valor = $remcliente->cliente->moneda->valor;
        $cctotales = $this->get_cctotales_cliente($cliente_id);
        $cortes = collect();
        $cortes_temp = collect($this->org_remisiones($cctotales));
        $cortes_temp->map(function($ct) use(&$cortes, $valor){
            $remdepositos = collect();
            collect($ct['remdepositos'])->map(function($rd) use(&$remdepositos, $valor){
                $remdepositos->push($this->set_remdeposito($rd, $valor));
            });

            $remisiones = collect();
            collect($ct['remisiones'])->map(function($r) use(&$remisiones, $valor){
                $remisiones->push([
                    'cerrado_por' => $r->cerrado_por,
                    'cliente_id' => $r->cliente_id,
                    'corte_id' => $r->corte_id,
                    'created_at' => $r->created_at,
                    'deleted_at' => $r->deleted_at,
                    'destino' => $r->destino,
                    'estado' => $r->estado,
                    'fecha_creacion' => $r->fecha_creacion,
                    'fecha_devolucion' => $r->fecha_devolucion,
                    'fecha_entrega' => $r->fecha_entrega,
                    'id' => $r->id,
                    'pagos' => $r->pagos * $valor,
                    'paqueteria_id' => $r->paqueteria_id,
                    'responsable' => $r->responsable,
                    'total' => $r->total * $valor,
                    'total_devolucion' => $r->total_devolucion * $valor,
                    'total_pagar' => $r->total_pagar * $valor,
                    'updated_at' => $r->updated_at,
                    'user_id' => $r->user_id,
                ]);
            });

            $cortes->push([
                'cliente' => $ct['cliente'],
                'cliente_id' => $ct['cliente_id'],
                'corte' => $ct['corte'],
                'corte_id' => $ct['corte_id'],
                'corte_id_favor' => $ct['corte_id_favor'], 
                'final' => $ct['final'],
                'inicio' => $ct['inicio'],
                'remdepositos' => $remdepositos,
                'remisiones' => $remisiones,
                'total' => $ct['total'] * $valor,
                'total_devolucion' => $ct['total_devolucion'] * $valor,
                'total_favor' => $ct['total_favor'] * $valor,
                'total_pagar' => $ct['total_pagar'] * $valor,
                'total_pagos' => $ct['total_pagos'] * $valor,
                'visible' => $ct['visible'],
            ]);
        });

        $adeudos = collect();
        $adeudos_temp = Adeudo::where('cliente_id', $cliente_id)->with('corte')->orderBy('created_at', 'desc')->get();
        $adeudos_temp->map(function($at) use(&$adeudos, $valor){
            $adeudos->push([
                'cliente_id' => $at->cliente_id,
                'corte' => $at->corte,
                'corte_id' => $at->corte_id,
                'created_at' => $at->created_at,
                'dias' => $at->dias,
                'id' => $at->id,
                'ingresado_por' => $at->ingresado_por,
                'rango' => $at->rango,
                'remdeposito_id' => $at->remdeposito_id,
                'saldo_inicial' => $at->saldo_inicial * $valor,
                'saldo_pagado' => $at->saldo_pagado * $valor,
                'saldo_pendiente' => $at->saldo_pendiente * $valor,
                'updated_at' => $at->updated_at
            ]);
        });
        $data = [
            'cliente_id' => $cliente_id,
            'name'  => $remcliente->cliente->name,
            'moneda' => $remcliente->cliente->moneda,
            'total' => $remcliente->total * $valor,
            'total_pagos' => $remcliente->total_pagos * $valor,
            'total_devolucion' => $remcliente->total_devolucion * $valor,
            'total_pagar' => $remcliente->total_pagar * $valor,
            'cortes' => $cortes,
            'adeudos' => $adeudos
        ]; 
        return response()->json($data);
    }

    // ASIGNAR VALORES PARA REMDEPOSITO
    public function set_remdeposito($remdeposito, $valor){
        return [
            'corte' => $remdeposito->corte,
            'corte_id' => $remdeposito->corte_id,
            'created_at' => $remdeposito->created_at,
            'deleted_at' => $remdeposito->deleted_at,
            'fecha' => $remdeposito->fecha,
            'foto' => $remdeposito->foto,
            'id' => $remdeposito->id,
            'ingresado_por' => $remdeposito->ingresado_por,
            'nota' => $remdeposito->nota,
            'pago' => $remdeposito->pago * $valor,
            'remcliente_id' => $remdeposito->remcliente_id,
            'revisado' => $remdeposito->revisado,
            'tipo' => $remdeposito->tipo,
            'updated_at' => $remdeposito->updated_at
        ];
    }
}
