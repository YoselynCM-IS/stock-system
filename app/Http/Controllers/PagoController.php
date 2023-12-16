<?php

namespace App\Http\Controllers;

use App\Exports\EdoCuentaExport;
use Illuminate\Http\Request;
use App\Devolucione;
use App\Remisione;
use App\Deposito;
use App\Vendido;
use App\Remcliente;
use App\Cctotale;
use App\Remdeposito;
use Carbon\Carbon;
use App\Fecha;
use App\Pago;
use App\Dato;
use Excel;
use PDF;

class PagoController extends Controller
{
    public function pagos_remision_cliente(Request $request){
        $cliente_id = $request->cliente_id;
        $remisiones = Remisione::where('cliente_id', $cliente_id)
                    ->where('total_pagar', '>', 0)
                    ->where(function ($query) {
                        $query->where('estado', '=', 'Proceso')
                            ->orWhere('estado', '=', 'Terminado');
                    })->orderBy('id','desc')
                    ->with('cliente')->paginate(20);
        return response()->json($remisiones);
    }

    // MOSTRAR DATOS
    // Función utilizada en DevoluciónComponent y PagosComponent
    public function datos_vendidos(Request $request){
        $remision_id = $request->remision_id;
        $vendidos = Vendido::where('remisione_id', $remision_id)->with('libro')->with('pagos')->with('dato')->get();
        $depositos = Deposito::where('remisione_id', $remision_id)->get();
        return response()->json(['vendidos' => $vendidos, 'depositos' => $depositos]);
    } 

    // GUARDAR DEPOSITO DE REMISIÓN
    // Función utilizada en PagosComponent
    public function deposito_remision(Request $request){
        $remision = Remisione::whereId($request->remision_id)->first();
        $remcliente = Remcliente::where('cliente_id', $remision->cliente_id)->first();
        $monto = (float) $request->monto;
        $pagos = $remision->pagos + $monto;
        $total_pagar = $remision->total_pagar - $monto;
        \DB::beginTransaction();
        try{
            Deposito::create([
                'remisione_id' => $request->remision_id,
                'pago' => $monto,
                'entregado_por' => $request->entregado_por,
                'ingresado_por' => auth()->user()->name
            ]);
            
            $remision->update([
                'pagos' => $pagos,
                'total_pagar' => $total_pagar
            ]);

            if ((int) $total_pagar === 0){
                $this->restantes_to_cero($remision);
                $remision->update(['estado' => 'Terminado']);
            }

            // ACTUALIZA LA CUENTA DEL CORTE CORRESPONDIENTE
            $cctotale = Cctotale::where([
                'cliente_id' => $remision->cliente_id,
                'corte_id'  => $remision->corte_id
            ])->first();
            $cctotale->update([
                'total_pagos' => $cctotale->total_pagos + $monto,
                'total_pagar' => $cctotale->total_pagar - $monto
            ]);
            
            $remcliente->update([
                'total_pagos' => $remcliente->total_pagos + $monto, 
                'total_pagar' => $remcliente->total_pagar - $monto
            ]);
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        
        return response()->json($remision);
    }

    public function store_gral(Request $request){
        $cliente_id = $request->cliente_id;
        $remision = Remcliente::where('cliente_id', $cliente_id)->first();
        try{
            \DB::beginTransaction();
            $monto = (float) $request->monto;
            $total_pagos = $remision->total_pagos + $monto;
            $total_pagar = $remision->total_pagar - $monto;
            Remdeposito::create([
                'remcliente_id' => $remision->id,
                'pago' => $monto,
                'fecha' => $request->fecha,
                'nota' => $request->nota,
                'ingresado_por' => auth()->user()->name
            ]);
            $remision->update([
                'total_pagos' => $total_pagos, 
                'total_pagar' => $total_pagar
            ]);

            if((float) $total_pagar == 0){
                $remisiones = Remisione::where(['cliente_id' => $cliente_id, 'estado' => 'Proceso'])->get();
                foreach($remisiones as $rem){
                    $total_pagar = $rem->total_pagar;
                    $pagos = $rem->pagos + $total_pagar;
                    $rem->update([
                        'pagos' => $pagos,
                        'total_pagar'   => 0,
                        'estado' => 'Terminado'
                    ]);
                }
            }
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json($remision);
    }

    // ACTUALIZAR LAS UNIDADES RESTANTES DE LAS REMISIONES
    // SOLO SI EN LA REMISIÓN SE REALIZO UN DEPOSITO
    public function restantes_to_cero($remision) {
        Devolucione::where('remisione_id', $remision->id)->update([
            'unidades_resta' => 0,
            'total_resta' => 0
        ]);
    }

    public function depositos_cliente(Request $request){
        $id = $request->id;
        $cliente_id = $request->cliente_id;

        $remdepositos = Remdeposito::where('remcliente_id', $id)
                                    ->orderBy('created_at', 'desc')->get();
        
        $remisiones = Remisione::where('cliente_id', $cliente_id)->get();
        $ids = [];
        $remisiones->map(function($remision) use(&$ids){
            $ids[] = $remision->id;
        });
        
        $depositos = Deposito::whereIn('remisione_id', $ids)->orderBy('created_at', 'desc')->get();
        return response()->json(['remdepositos' => $remdepositos, 'depositos' => $depositos]);
    }

    public function guardar_revision(Request $request){
        \DB::beginTransaction();
        try{ 
            $deposito = Remdeposito::whereId($request->id)->update(['revisado' => true]);
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json($deposito);
    }


    // NO SE UTILIZA
    // REGISTRAR PAGO DE REMISIÓN (POR UNIDADES)
    // Función utilizada en AdeudosComponent, DevoluciónAdeudosComponent, DevoluciónComponent, PagosComponent
    public function store(Request $request){
        try{
            \DB::beginTransaction();
            // Buscar remisión
            $remision = Remisione::whereId($request->id)->first();
            $entregado_por = $request->entregado_por;
            $pagos = 0;
            foreach($request->vendidos as $vendido){
                $unidades_base = $vendido['unidades_base'];
                $total_base = $vendido['total_base'];

                if($unidades_base != 0){
                    // Guardar pagos por libro
                    Pago::create([
                        'user_id' => auth()->user()->id,
                        'vendido_id' => $vendido['id'],
                        'unidades' => $unidades_base,
                        'pago' => $total_base,
                        'entregado_por' => $entregado_por
                    ]);

                    // Buscar el dato vendido
                    $d_vendido = Vendido::whereId($vendido['id'])->first();
                    $unidades = $d_vendido->unidades + $unidades_base;
                    $total = $d_vendido->total + $total_base;
                    $unidades_resta = $d_vendido->unidades_resta - $unidades_base;
                    $total_resta = $d_vendido->total_resta - $total_base;
                    
                    $d_vendido->update([
                        'unidades' => $unidades, 
                        'unidades_resta' => $unidades_resta,
                        'total' => $total,
                        'total_resta' => $total_resta
                    ]); 
                    
                    // Actualizar los datos de devolución
                    Devolucione::where('dato_id', $vendido['dato']['id'])->update([
                        'unidades_resta' => $unidades_resta,
                        'total_resta' => $total_resta
                    ]);
                }
                $pagos += $total_base;
            }
            
            $total_pagar = $remision->total_pagar - $pagos;
            $total_pagos = $remision->pagos + $pagos; 
            $remision->update([
                'pagos' => $total_pagos,
                'total_pagar'   => $total_pagar
            ]);
            if ((int) $total_pagar === 0) {
                if ($remision->depositos->count() > 0)
                    $this->restantes_to_cero($remision);
                $remision->update(['estado' => 'Terminado']);
            }
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
        }
        
        return response()->json($remision);
    }

    // DESCARGAR EL ESTADO DE CUENTA
    public function download_edocuenta($cliente_id){
        $remcliente = Remcliente::where('cliente_id', $cliente_id)
                        ->with('cliente')->first();
        $remisiones = Remisione::where('cliente_id', $cliente_id)
                    ->where('estado', 'Proceso')
                    ->orderBy('created_at', 'desc')->get();
    
        $ids = [];
        $remisiones->map(function($remision) use(&$ids){
            $ids[] = $remision->id;
        });

        $year = Carbon::now()->format('Y');
        
        $remdepositos = Remdeposito::where('remcliente_id', $remcliente->id)
                    ->where('created_at', 'like', '%'.$year.'%')
                    ->orderBy('created_at', 'desc')->get();
        $fechas = Fecha::whereIn('remisione_id', $ids)->with('libro')
                    ->where('created_at', 'like', '%'.$year.'%')
                    ->orderBy('created_at', 'desc')->get();
        $depositos = Deposito::whereIn('remisione_id', $ids)
                    ->where('created_at', 'like', '%'.$year.'%')
                    ->orderBy('created_at', 'desc')->get();
        
        $data = [
            'date' => Carbon::now(),
            'remcliente' => $remcliente,
            'remisiones' => $remisiones,
            'remdepositos' => $remdepositos,
            'fechas' => $fechas,
            'depositos' => $depositos,
            'total_remisiones' => $remisiones->sum('total'),
            'total_fechas' => $fechas->sum('total'),
            'total_remdepositos' => $remdepositos->sum('pago') + $depositos->sum('pago')
        ];
        $pdf = PDF::loadView('download.excel.pagos.edo_cuenta', $data);
        return $pdf->download('edo-cuenta.pdf');
        // return Excel::download(new EdoCuentaExport($cliente_id), 'edo-de-cuenta.xlsx');
    }
}
