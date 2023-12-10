<?php

namespace App\Http\Controllers;

use App\Exports\RemisionesExport;
use App\Exports\RemisionExport;
use App\Exports\RemisionesGExport;
use App\Helper\CollectionHelper;
use App\Exports\GAccountExport;
use Illuminate\Http\Request;
use App\Destinatario;
use NumerosEnLetras;
use App\Devolucione;
use App\Remdeposito;
use App\Remcliente;
use App\Comentario;
use App\Paqueteria;
use Carbon\Carbon;
use App\Remisione;
use App\Donacione;
use App\Deposito;
use App\Cctotale;
use App\Vendido;
use App\Cliente;
use App\Reporte;
use App\Libro;
use App\Fecha;
use App\Corte;
use App\Dato;
use App\Pago;
use App\Code;
use App\Pack;
use Excel;
use PDF;
use DB;
// use App\Entrada;
// use App\Registro;
// use App\Ectotale;
// use App\Editoriale;
// use App\Enteditoriale;

class RemisionController extends Controller
{
    // VISTA PARA LOS PEDIDOS DE LOS CLIENTES
    public function lista(){
        return view('information.remisiones.lista');
    }

    // MOSTRAR TODAS LAS REMISIONES
    public function index(){
        $remisiones = Remisione::with(['cliente:id,name'])
                    ->withCount('depositos')
                    ->orderBy('id','desc')->paginate(20);
        return response()->json($remisiones);
    }

    // --- BUSQUEDAS ---
    // BUSCAR REMISIÓN POR NUMERO
    // Función utilizada en ListadoComponent y RemisionesComponent
    public function por_numero(Request $request){
        $remision = Remisione::whereId($request->num_remision)->with('cliente')->first();
        return response()->json(['remision' => $remision]);
    }

    // MOSTRAR REMISIONES POR CLIENTE
    // Función utilizada en ListadoComponent y RemisionesComponent
    public function buscar_por_cliente(Request $request){
        $id = $request->id;
        $cliente = Cliente::find($id);

        // INICIO TOTALES
        $totales = $this->get_remcliente_totales($id);
        // FIN TOTALES

        $remisiones = Remisione::where('cliente_id', $cliente->id)
                        ->orderBy('id','desc')->with('cliente')
                        ->paginate(20);

        return response()->json(['remisiones' => $remisiones, 'totales' => $totales]);
    }

    public function get_remcliente_totales($id){
        $remcliente = Remcliente::where('cliente_id', $id)->first();
        return [
            'total' => $remcliente->total,
            'total_devolucion' => $remcliente->total_devolucion,
            'total_pagos' => $remcliente->total_pagos,
            'total_pagar' => $remcliente->total_pagar
        ];
    }

    public function f_totales($ids, $remdepositos, $depositos){
        $sum_totales = Remisione::whereIn('id', $ids)
                    ->select(
                        \DB::raw('SUM(total) as total'),
                        \DB::raw('SUM(total_devolucion) as total_devolucion')
                    )->get();
        $total = $sum_totales[0]['total'];
        $total_devolucion = $sum_totales[0]['total_devolucion'];
        $total_pagos = $remdepositos + $depositos;
        return [
            'total' => $total,
            'total_devolucion' => $total_devolucion,
            'total_pagos' => $total_pagos,
            'total_pagar' => $total - ($total_devolucion + $total_pagos)
        ];
    }

    // MOSTRAR REMISIONES POR ESTADO
    // Función utilizado en ListadoComponent
    public function buscar_por_estado(Request $request){
        $estado = $request->estado;
        $cliente_id = $request->cliente_id;

        if($estado === 'cancelado'){
            $remisiones = $this->pag_estado_SF(4, $cliente_id);
        }
        if($estado === 'no_entregado'){
            $remisiones = $this->pag_estado_SF(1, $cliente_id);
        }
        if($estado === 'entregado'){
            $remisiones = $this->pag_estado_SF(2, $cliente_id);
        }
        if($estado === 'pagado'){
            $remisiones = $this->pag_pagado_SF($cliente_id);
        }
        return response()->json($remisiones);
    }

    // FUNCIÓN PARA LA BUSQUEDA DE REMISIÓN POR ESTADO SIN FECHA PAGINADO
    public function pag_estado_SF($estado, $cliente_id){
        if ($estado == 1 || $estado == 4) {
            // REMISIONES INICIADAS Y CANCELADAS
            if($cliente_id === null){
                return Remisione::where('estado',$estado)->orderBy('id','desc')
                                ->with('cliente:id,name')->paginate(20);
            } else {
                return Remisione::where('estado',$estado)
                            ->where('cliente_id', $cliente_id)
                            ->orderBy('id','desc')
                            ->with('cliente:id,name')->paginate(20);
            } 
        }
        if ($estado == 2){
            // REMISIONES EN PROCESO
            if($cliente_id === null){
                return Remisione::where('total_pagar', '>', 0)
                    ->where('estado', 'Proceso')
                    ->orderBy('id','desc')
                    ->with('cliente:id,name')->paginate(20);
            } else {
                return Remisione::where('cliente_id', $cliente_id)
                    ->where('total_pagar', '>', 0)
                    ->where('estado', 'Proceso')
                    ->orderBy('id','desc')
                    ->with('cliente:id,name')->paginate(20);
            }
        }
    }

    // FUNCIÓN PARA LA BUSQUEDA DE REMISIÓN POR ESTADO (PAGADO) SIN FECHA PAGINADO
    public function pag_pagado_SF($cliente_id){
        if($cliente_id === null){
            return Remisione::where('total_pagar', '=', 0)
                        ->where(function ($query) {
                            $query->where('pagos', '>', 0)
                                    ->orWhere('total_devolucion', '>', 0);
                        })->orderBy('id','desc')
                        ->with('cliente:id,name')->paginate(20);
        } else {
            return Remisione::where('cliente_id', $cliente_id)
                        ->where('total_pagar', '=', 0)
                        ->where(function ($query) {
                            $query->where('pagos', '>', 0)
                                    ->orWhere('total_devolucion', '>', 0);
                        })->orderBy('id','desc')
                        ->with('cliente:id,name')->paginate(20);
        }
    }

    // FUNCIÓN PARA LA BUSQUEDA DE REMISIÓN POR ESTADO CON FECHA
    public function estadoS_CF($estado, $inicio, $final){
        if ($estado == 1 || $estado == 4) {
            // REMISIONES INICIADAS Y CANCELADAS
            return Remisione::where('estado',$estado)
                ->whereBetween('fecha_creacion', [$inicio, $final])
                ->orderBy('id','desc')
                ->with('cliente:id,name')->get();
        }
        if ($estado == 2){
            // REMISIONES EN PROCESO
            return Remisione::where('total_pagar', '>', 0)
                ->whereBetween('fecha_creacion', [$inicio, $final])
                ->orderBy('id','desc')
                ->with('cliente:id,name')->get();
        }
    }
    // FUNCIÓN PARA LA BUSQUEDA DE REMISIÓN POR ESTADO SIN FECHA
    public function estadoS_SF($estado, $cliente_id){
        if ($estado == 1 || $estado == 4) {
            // REMISIONES INICIADAS Y CANCELADAS
            if($cliente_id === null){
                return Remisione::where('estado',$estado)->orderBy('id','desc')
                                ->with('cliente:id,name')->get();
            } else {
                return Remisione::where('estado',$estado)
                            ->where('cliente_id', $cliente_id)
                            ->orderBy('id','desc')
                            ->with('cliente:id,name')->get();
            } 
        }
        if ($estado == 2){
            // REMISIONES EN PROCESO
            if($cliente_id === null){
                return Remisione::where('total_pagar', '>', 0)
                    ->where('estado', 'Proceso')
                    ->orderBy('id','desc')
                    ->with('cliente:id,name')->get();
            } else {
                return Remisione::where('cliente_id', $cliente_id)
                    ->where('total_pagar', '>', 0)
                    ->where('estado', 'Proceso')
                    ->orderBy('id','desc')
                    ->with('cliente:id,name')->get();
            }
        }
    }

    // FUNCIÓN PARA LA BUSQUEDA DE REMISIÓN POR ESTADO (PAGADO) CON FECHA
    public function pagado_CF($inicio, $final){
        return Remisione::where('total_pagar', '=', 0)
                        ->where(function ($query) {
                            $query->where('pagos', '>', 0)
                                    ->orWhere('total_devolucion', '>', 0);
                        })
                        ->whereBetween('fecha_creacion', [$inicio, $final])
                        ->withCount('depositos')
                        ->orderBy('cliente_id','asc')
                        ->with('cliente:id,name')->get();
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
                        ->with('cliente:id,name')->get();
        } else {
            return Remisione::where('cliente_id', $cliente_id)
                        ->where('total_pagar', '=', 0)
                        ->where(function ($query) {
                            $query->where('pagos', '>', 0)
                                    ->orWhere('total_devolucion', '>', 0);
                        })
                        ->orderBy('id','desc')
                        ->with('cliente:id,name')->get();
        }
    }

    public function f_ids($remisiones){
        $ids = [];
        $remisiones->map(function($remision) use(&$ids){
            $ids[] = $remision->id;
        });
        return $ids;
    }

    // MOSTRAR REMISIONES POR FECHAS
    // Función utilizada en ListadoComponent
    public function buscar_por_fecha(Request $request){
        $cliente_id = $request->cliente_id;
        $inicio = $request->inicio;
        $final = $request->final;
        if($cliente_id === null){
            $remisiones = Remisione::whereBetween('fecha_creacion', [$inicio, $final])
                ->orderBy('id','desc')
                ->with('cliente')->paginate(20); 
        } else{
            $cliente = Cliente::find($cliente_id);

            $remisiones = Remisione::where('cliente_id', $cliente->id)
                        ->whereBetween('fecha_creacion', [$inicio, $final])
                        ->withCount('depositos')->with('cliente')
                        ->orderBy('id','desc')->paginate(20);
        }
        return response()->json($remisiones);
    }

    public function get_totales_fecha(Request $request){
        $cliente_id = $request->cliente_id;
        $inicio = $request->inicio;
        $final = $request->final;
        if($cliente_id === null){
            // INICIO TOTALES
            $remisiones_ids = Remisione::whereBetween('fecha_creacion', [$inicio, $final])
                    ->whereNotIn('estado', ['Cancelado'])
                    ->select('id')->get();
            $ids = $this->f_ids($remisiones_ids);
            $depositos = Deposito::whereIn('remisione_id', $ids)->sum('pago');
            $clientes_ids = Remisione::whereIn('id', $ids)->select('cliente_id')->get();
            $rc_ids = [];
            $clientes_ids->map(function($ci) use(&$rc_ids){
                $cliente = Cliente::find($ci->cliente_id);
                $rc_ids[] = $cliente->remcliente->id;
            });
            $remdepositos = Remdeposito::whereIn('remcliente_id', $rc_ids)
                    ->whereBetween('created_at', [$inicio.' 00:00:00', $final.' 23:59:59'])
                    ->sum('pago');
            $totales = $this->f_totales($ids, $remdepositos, $depositos);
            // FIN TOTALES
        } else {
            // INICIO TOTALES
            $cliente = Cliente::find($cliente_id);
            $remisiones_ids = Remisione::where('cliente_id', $cliente->id)
                ->whereBetween('fecha_creacion', [$inicio, $final])
                ->whereNotIn('estado', ['Cancelado'])
                ->select('id')->get();
            $ids = $this->f_ids($remisiones_ids);
            $depositos = Deposito::whereIn('remisione_id', $ids)->sum('pago');
            $remdepositos = Remdeposito::where('remcliente_id', $cliente->remcliente->id)
                ->whereBetween('created_at', [$inicio.' 00:00:00', $final.' 23:59:59'])
                ->sum('pago');
            $totales = $this->f_totales($ids, $remdepositos, $depositos);
            // FIN TOTALES
        }
        return response()->json($totales);
    }

    // --- DESCARGAR ---
    // DESCARGAR REPORTE GENERAL Y DETALLADO
    public function imprimirEstado($estado, $cliente_id, $inicio, $final){
        if($estado === 'cancelado'){
            if($final != '0000-00-00'){ $remisiones = $this->estadoS_CF(4, $inicio, $final); }
            else { 
                if($cliente_id === 'null'){ $remisiones = $this->estadoS_SF(4, null); }
                else { $remisiones = $this->estadoS_SF(4, $cliente_id); } 
            }
        }
        if($estado === 'no_entregado'){
            if($final != '0000-00-00'){ $remisiones = $this->estadoS_CF(1, $inicio, $final); }
            else { 
                if($cliente_id === 'null'){ $remisiones = $this->estadoS_SF(1, null); }
                else { $remisiones = $this->estadoS_SF(1, $cliente_id); }
            }
        }
        if($estado === 'entregado'){
            if($final != '0000-00-00'){ $remisiones = $this->estadoS_CF(2, $inicio, $final); }
            else {
                if($cliente_id === 'null'){ $remisiones = $this->estadoS_SF(2, null); }
                else { $remisiones = $this->estadoS_SF(2, $cliente_id); }
            }
        }
        if($estado === 'pagado'){
            if($final != '0000-00-00'){ $remisiones = $this->pagado_CF($inicio, $final); }
            else {
                if($cliente_id === 'null'){ $remisiones = $this->pagado_SF(null);  }
                else { $remisiones = $this->pagado_SF($cliente_id);  }
                
            }
        }

        $valores = $this->totales($remisiones);
        $data['remisiones'] = $remisiones;
        $data['estado'] = $estado;
        $data['inicio'] = $inicio;
        $data['final'] = $final;
        $data['fecha'] = $valores['fecha'];
        $data['totales'] = $valores['totales'];
        $pdf = PDF::loadView('download.pdf.remisiones.reporte-estado-gral', $data);
        return $pdf->download('reporte-estado-gral.pdf');
    }

    // DESCARGAR PDF DE REMISIONES POR FECHA
    // Función utilizada en ListadoComponent
    public function imprimirFecha($inicio, $final){
        $remisiones = Remisione::whereBetween('fecha_creacion', [$inicio, $final])
                ->whereNotIn('estado', ['Iniciado', 'Cancelado'])
                ->orderBy('cliente_id','asc')
                ->with('cliente')->get();

        $valores = $this->totales($remisiones);
        $data['fecha'] = $valores['fecha'];
        $data['inicio'] = $inicio;
        $data['final'] = $final;
        $data['remisiones'] = $remisiones;
        $data['totales'] = $valores['totales'];
        $pdf = PDF::loadView('download.pdf.remisiones.reporte-fecha-gral', $data);
        return $pdf->download('reporte-fecha-gral.pdf');
    }

    
    // IMPRIMIR PDF CON LOS DATOS DE LAS REMISIONES POR CLIENTE
    // Función utilizada en ListadoComponent
    public function imprimirCliente($cliente_id, $inicio, $final){
        if($inicio != '0000-00-00' && $final != '0000-00-00'){
            $remisiones = Remisione::where('cliente_id', $cliente_id)
                            ->whereBetween('fecha_creacion', [$inicio, $final])
                            ->whereNotIn('estado', ['Iniciado', 'Cancelado'])
                            ->orderBy('id','desc')
                            ->get();
        }
        else {
            $remisiones = Remisione::where('cliente_id', $cliente_id)
                    ->whereNotIn('estado', ['Iniciado', 'Cancelado'])
                    ->orderBy('id','desc')
                    ->get();
        }
        $valores = $this->totales($remisiones);
        $data['fecha'] = $valores['fecha'];
        $data['inicio'] = $inicio;
        $data['final'] = $final;
        $data['remisiones'] = $remisiones;
        $data['totales'] = $valores['totales'];
        $pdf = PDF::loadView('download.pdf.remisiones.reporte-cliente-gral', $data);
        return $pdf->download('reporte-cliente-gral.pdf');
    }

    public function down_gral_excel($cliente_id, $inicio, $final, $estado){
        return Excel::download(new RemisionesGExport($cliente_id, $inicio, $final, $estado), 'reporte-remisiones.xlsx');
    }

    public function down_remisiones_excel($cliente_id, $inicio, $final, $estado){
        return Excel::download(new RemisionesExport($cliente_id, $inicio, $final, $estado), 'reporte-detallado.xlsx');
    }
    
    public function down_remisiones_pdf($cliente_id, $inicio, $final, $estado){
        if($cliente_id === 'null' && $inicio === '0000-00-00' && $final === '0000-00-00' && $estado === 'null'){
            $remisiones = Remisione::with(['cliente:id,name'])->with('datos.libro')
                    ->orderBy('id','desc')
                    ->get();
        }
        if($cliente_id !== 'null' && $inicio === '0000-00-00' && $final === '0000-00-00' && $estado === 'null'){
            $remisiones = Remisione::where('cliente_id', $cliente_id)
                    ->orderBy('id','desc')
                    ->get();
        }
        if($cliente_id !== 'null' && $inicio != '0000-00-00' && $final != '0000-00-00' && $estado === 'null'){
            $remisiones = Remisione::where('cliente_id', $cliente_id)
                            ->whereBetween('fecha_creacion', [$inicio, $final])
                            ->orderBy('id','desc')
                            ->get();
        }
        if($cliente_id === 'null' && $inicio != '0000-00-00' && $final != '0000-00-00' && $estado === 'null'){
            $remisiones = Remisione::whereBetween('fecha_creacion', [$inicio, $final])
                            ->orderBy('cliente_id','asc')
                            ->with('cliente')->get();
        }
        if($estado !== 'null'){
            if($estado === 'cancelado'){
                if($cliente_id === 'null'){ $remisiones = $this->estadoS_SF(4, null); }
                else { $remisiones = $this->estadoS_SF(4, $cliente_id); } 
            }
            if($estado === 'no_entregado'){
                if($cliente_id === 'null'){ $remisiones = $this->estadoS_SF(1, null); }
                else { $remisiones = $this->estadoS_SF(1, $cliente_id); }
            }
            if($estado === 'entregado'){
                if($cliente_id === 'null'){ $remisiones = $this->estadoS_SF(2, null); }
                else { $remisiones = $this->estadoS_SF(2, $cliente_id); }
            }
            if($estado === 'pagado'){
                if($cliente_id === 'null'){ $remisiones = $this->pagado_SF(null);  }
                else { $remisiones = $this->pagado_SF($cliente_id);  }
            }
        }

        $valores = $this->totales($remisiones);
        $data['fecha'] = $valores['fecha'];
        $data['inicio'] = $inicio;
        $data['final'] = $final;
        $data['estado'] = $estado;
        $data['remisiones'] = $remisiones;
        $data['totales'] = $valores['totales'];
        $pdf = PDF::loadView('download.pdf.remisiones.reporte-remisiones-gral', $data);
        return $pdf->download('reporte-remisiones.pdf');
    }

    // OBTENER TOTALES DE LAS REMISIONES
    public function totales($remisiones){
        $total_salida = 0;
        $total_pagos = 0;
        $total_devolucion = 0;
        $total_donacion = 0;
        $total_pagar = 0;

        foreach($remisiones as $r){
            if($r->estado === 'Proceso' || $r->estado === 'Terminado'){
                $total_salida += $r->total;
                $total_pagos += $r->pagos;
                $total_devolucion += $r->total_devolucion;
                $total_donacion += $r->total_donacion;
                $total_pagar += $r->total_pagar;  
            }          
        }
        $datos = [
            'fecha' => $fecha = Carbon::now(),
            'totales' => [
                'total_salida' => $total_salida,
                'total_pagos' => $total_pagos,
                'total_devolucion' => $total_devolucion,
                'total_donacion' => $total_donacion,
                'total_pagar' => $total_pagar
            ]
        ];
        return $datos;
    }
    
    // MOSTRAR DEVOLUCIONES DE REMISION
    // Función utilizada en ListadoComponent, DevoluciónComponent, RemisionesComponent
    public function obtener_devoluciones(Request $request){
        $devoluciones = Devolucione::where('remisione_id', $request->remisione_id)
                    ->with([
                        'libro',
                        'dato.codes'
                    ])->get();
        
        $digitales = collect();
        $fisicos = collect();
        $devoluciones->map(function($devolucion) use (&$digitales, &$fisicos){
            if($devolucion->libro->type == 'digital')
                $digitales->push($devolucion->libro);
            if($devolucion->libro->type == 'venta')
                $fisicos->push($devolucion->libro);
        });

        $packs = collect();
        if($fisicos->count() > 0 && $digitales->count() > 0){
            $fisicos->map(function($fisico) use(&$packs, &$digitales){
                $p = Pack::where('libro_fisico', $fisico['id'])
                            ->whereIn('libro_digital', $digitales->pluck('id'))->first();
                $packs->push($p);
            });
        }

        $datos = collect();
        $devoluciones->map(function($devolucion) use (&$datos, $packs){
            $codes = null;
            $referencia = null;
            if($devolucion->libro->type == 'digital'){
                $codes = \DB::table('code_dato')
                        ->join('codes', 'code_dato.code_id', '=', 'codes.id')
                        ->select('codes.*')
                        ->where('code_dato.dato_id', $devolucion->dato_id)
                        ->where('code_dato.devolucion', false)->get();
                $lf = $packs->where('libro_digital', $devolucion->libro_id)->first();
                $referencia = $lf ? $lf->libro_fisico:null;
            }
            if($devolucion->libro->type == 'venta'){
                $ld = $packs->where('libro_fisico', $devolucion->libro_id)->first();
                $referencia = $ld ? $ld->libro_digital:null;
            }

            $datos->push([
                'created_at' => $devolucion->created_at,
                'dato' => $devolucion->dato,
                'dato_id' => $devolucion->dato_id,
                'id' => $devolucion->id,
                'libro' => $devolucion->libro,
                'libro_id' => $devolucion->libro_id,
                'remisione_id' => $devolucion->remisione_id,
                'total' => $devolucion->total,
                'total_base' => $devolucion->total_base,
                'total_resta' => $devolucion->total_resta,
                'unidades' => $devolucion->unidades,
                'unidades_base' => $devolucion->unidades_base,
                'unidades_resta' => $devolucion->unidades_resta,
                'updated_at' => $devolucion->updated_at,
                'codes' => $codes,
                'code_dato' => [],
                'scratch' => false,
                'defectuosos' => 0,
                'comentario' => null,
                'referencia' => $referencia,
                'status' => true
            ]);
        });
        return response()->json($datos);
    }

    // CANCELAR REMISIÓN
    // Función utilizada en ListadoComponent
    public function cancel(Request $request){
        $remision = Remisione::whereId($request->id)->first();
        \DB::beginTransaction();
        try{ 
            $scratch = collect();
            $remision->datos->map(function($dato) use(&$scratch){
                // REGRESAR EL NUMERO DE PIEZAS TOMADAS
                \DB::table('libros')->whereId($dato->libro_id)
                                    ->increment('piezas',  $dato->unidades);

                if($dato->libro->type == 'digital' && $dato->codes()->count() == 0){
                    $scratch->push([
                        'libro_id' => $dato->libro_id,
                        'unidades' => $dato->unidades
                    ]);
                }
                if($dato->libro->type == 'digital' && $dato->codes()->count() > 0){
                    // BORRAR CODIGOS
                    $dato->codes->map(function($code){
                        $code->update(['estado' => 'inventario']);
                    });
                    $dato->codes()->detach();
                }

                $reporte = 'registro la cancelación (remisión) de '.$dato->unidades.' unidades - '.$dato->libro->editorial.': '.$dato->libro->type.' '.$dato->libro->ISBN.' / '.$dato->libro->titulo.' para '.$dato->remisione_id.' / '.$dato->remisione->cliente->name;
                $this->create_report($dato->id, $reporte, 'libro', 'datos');
            });

            // DEVOLVER LIBROS A SCRATCH
            $all_libroid = $remision->datos->pluck('libro_id');
            $scratch->map(function($s) use($all_libroid){
                $p = Pack::where('libro_digital', $s['libro_id'])
                            ->whereIn('libro_fisico', $all_libroid)
                            ->first();
                $p->update(['piezas' => $p->piezas + $s['unidades']]);
            });

            // BORRAR LOS REGISTROS DE DEVOLUCION
            Devolucione::where('remisione_id', $remision->id)->delete();
            
            // ACTUALIZA LA CUENTA DEL CORTE CORRESPONDIENTE
            $cctotale = $this->get_cctotale($remision, $remision->cliente_id);
            $cctotale->update([
                'total' => $cctotale->total - $remision->total,
                'total_pagar' => $cctotale->total_pagar - $remision->total
            ]);

            // ACTUALIZAR LA CUENTA DEL CLIENTE
            $remcliente = Remcliente::where('cliente_id', $remision->cliente_id)->first();
            $remcliente->update([
                'total' => $remcliente->total - $remision->total,
                'total_pagar' => $remcliente->total_pagar - $remision->total
            ]);

            $remision->update(['estado' => 'Cancelado']);

            $reporte = 'cancelo la remisión '.$remision->id.' de '.$remision->cliente->name;
            $this->create_report($remision->id, $reporte, 'cliente', 'remisiones');
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
		}
        
        return response()->json($remision);
    }

    public function get_cctotale($remision, $cliente_id){
        $cctotale = Cctotale::where([
            'cliente_id' => $cliente_id,
            'corte_id'  => $remision->corte_id
        ])->first();

        if($cctotale == null){
            return Cctotale::create([
                'cliente_id' => $cliente_id,
                'corte_id'  => $remision->corte_id
            ]);
        }

        return $cctotale;
    }

    // GUARDAR REMISION *CHECK
    // Función utilizada en RemisionComponent
    public function store(Request $request){
        \DB::beginTransaction();
        try {
            $hoy = Carbon::now();
            $fecha_hoy = $hoy->format('Y-m-d');
            $corte_id = $this->search_corte_actual();
            
            $total = (double) $request->total;
            // CREAR REMISIÓN
            $remision = Remisione::create([
                'user_id' => auth()->user()->id,
                'corte_id' => $corte_id,
                'cliente_id' => $request->cliente['id'],
                'total' => $total,
                'total_pagar' => $total,
                'fecha_entrega' => $request->fecha_entrega,
                'estado' => 'Proceso',
                'fecha_creacion' => $fecha_hoy,
                'fecha_devolucion' => $fecha_hoy
            ]);
            
            $this->save_datos($request->datos, $remision);
            
            // ACTUALIZA LA CUENTA DEL CORTE CORRESPONDIENTE
            $cctotale = $this->get_cctotale($remision, $remision->cliente_id);
            $cctotale->update([
                'total' => $cctotale->total + $remision->total,
                'total_pagar' => $cctotale->total_pagar + $remision->total
            ]);
            

            // BUSCAR EL CLIENTE Y AFECTAR SU CUENTA GENERAL
            $remcliente = Remcliente::where('cliente_id', $remision->cliente_id)->first();
            if($remcliente === null){
                Remcliente::create([
                    'cliente_id' => $remision->cliente_id,
                    'total' => $remision->total,
                    'total_pagar' => $remision->total
                ]);
            } else {
                $remcliente->update([
                    'total' => $remcliente->total + $remision->total,
                    'total_pagar' => $remcliente->total_pagar + $remision->total
                ]);
            }
            
            $reporte = 'creo la remisión '.$remision->id.' para '.$remision->cliente->name;
            $this->create_report($remision->id, $reporte, 'cliente', 'remisiones');
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json();
    }

    public function save_datos($req_datos, $remision){
        $lista_datos = [];
        $scratch = collect();
        $request_datos = collect($req_datos);
        $hoy = Carbon::now();
        $request_datos->map(function($dato) use (&$scratch, &$lista_datos, $remision, $hoy){
            $libro_id = $dato['libro']['id'];
            $unidades = (int) $dato['unidades'];
            $lista_datos[] = [
                'remisione_id' => $remision->id,
                'libro_id'  => $libro_id,
                'costo_unitario' => (float) $dato['costo_unitario'],
                'unidades'  => $unidades,
                'total'     => (double) $dato['total'],
                'created_at' => $hoy,
                'updated_at' => $hoy
            ];

            if($dato['scratch']){
                $scratch->push([
                    'libro_id' => $libro_id,
                    'unidades' => $unidades
                ]);
            }
        });
        
        // CREAR REGISTROS DE DATOS
        Dato::insert($lista_datos);

        $lista_devoluciones = [];
        $lista_codes = collect();
        $datos = Dato::where('remisione_id', $remision->id)->get();
        $datos->map(function($dato) use(&$scratch, &$lista_devoluciones, &$lista_codes, $hoy){
            $libro_id = $dato->libro_id;
            $lista_devoluciones[] = [
                'remisione_id' => $dato->remisione_id,
                'dato_id'   => $dato->id,
                'libro_id' => $libro_id,
                'unidades_resta' => $dato->unidades,
                'total_resta' => $dato->total,
                'created_at' => $hoy,
                'updated_at' => $hoy
            ];

            $s = $scratch->where('libro_id', $dato->libro_id)->first();
            if($dato->libro->type == 'digital'){
                // $s = $scratch->where('libro_id', $dato->libro_id)->first();
                if($s == null){
                    $lista_codes->push([
                        'dato_id'   => $dato->id,
                        'libro_id'  => $dato->libro_id,
                        'unidades'  => $dato->unidades
                    ]);
                } else {
                    $p = Pack::where('libro_digital', $dato->libro_id)
                            ->whereIn('libro_fisico', $scratch->pluck('libro_id'))->first();
                    $dato->update(['pack_id' => $p->id]);
                    $p->update(['piezas' => $p->piezas - $s['unidades']]);
                }
            }
            if($dato->libro->type == 'venta'){
                if($s !== null){
                    $p = Pack::where('libro_fisico', $dato->libro_id)
                            ->whereIn('libro_digital', $scratch->pluck('libro_id'))->first();
                    $dato->update(['pack_id' => $p->id]);
                }
            }
            
            // DISMINUIR PIEZAS DE LOS LIBROS
            \DB::table('libros')->whereId($libro_id)
                                ->decrement('piezas',  $dato->unidades);

            $reporte = 'registro la salida (remision) de '.$dato->unidades.' unidades - '.$dato->libro->editorial.': '.$dato->libro->type.' '.$dato->libro->ISBN.' / '.$dato->libro->titulo.' para '.$dato->remisione_id.' / '.$dato->remisione->cliente->name;
            $this->create_report($dato->id, $reporte, 'libro', 'datos');
        });

        // CREAR REGISTROS DE DEVOLUCION
        Devolucione::insert($lista_devoluciones);

        $hoy = Carbon::now();
        $lista_codes->map(function($lc, $hoy){
            $this->get_codes($lc['libro_id'], $lc['unidades'], $lc['dato_id']);
        });
    }

    public function get_codes($libro_id, $unidades, $dato_id){
        $codes = Code::where('libro_id', $libro_id)
                            ->where('estado', 'inventario')
                            ->where('tipo', 'alumno')
                            ->orderBy('created_at', 'asc')
                            ->limit($unidades)
                            ->get();
        
        $dato = Dato::find($dato_id);
        $codes->map(function($code) use (&$dato){
            $code->update(['estado' => 'ocupado']);
            $dato->codes()->attach($code->id);
        });
    }

    // MARCAR COMO ENTREGADA UNA REMISIÓN
    // Función utilizada en RemisionesComponent
    public function registrar_vendidos(Request $request){
        $remision = Remisione::whereId($request->remision)->with('datos.libro')->first();
        try {
            if(Vendido::where('remisione_id', $remision->id)->count() === 0){  
                \DB::beginTransaction();
                $remision->update([
                    'estado' => 'Proceso',
                    'responsable' => $request->responsable
                ]);
                $total_pagar = $remision->total;
                $remision->update(['total_pagar' => $total_pagar]);
                foreach($remision->datos as $dato){
                    $vendido = Vendido::create([
                        'remisione_id' => $dato->remisione_id,
                        'dato_id'   => $dato->id,
                        'libro_id' => $dato->libro_id, 
                        'unidades_resta' => $dato->unidades,
                        'total_resta' => $dato->total,
                    ]);
                    $devolucion = Devolucione::create([
                        'remisione_id' => $dato->remisione_id,
                        'dato_id'   => $dato->id,
                        'libro_id' => $dato->libro_id,
                        'unidades_resta' => $dato->unidades,
                        'total_resta' => $dato->total,
                        'fecha_devolucion' => Carbon::now()->format('Y-m-d')
                    ]);
                }
                $remcliente = Remcliente::where('cliente_id', $remision->cliente_id)->first();
                if($remcliente === null){
                    Remcliente::create([
                        'cliente_id' => $remision->cliente_id,
                        'total' => $total_pagar,
                        'total_pagar' => $total_pagar
                    ]);
                } else {
                    $remcliente->update([
                        'total' => $remcliente->total + $total_pagar,
                        'total_pagar' => $remcliente->total_pagar + $total_pagar
                    ]);
                }
                
                \DB::commit();
            }
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json(['remision' => $remision]);
    }
    // OBTENER TODAS LAS REMISIONES DE LOS CLIENTES
    // Función utilizada en ListadoComponent
    public function todos(){
        $remisiones = Remisione::with('cliente:id,name')->orderBy('id','desc')->get();
        return response()->json($remisiones);
    } 

    // DESCARGAR REMISIÓN
    public function imprimirSalida($id){ 
        $remision = Remisione::whereId($id)->with('datos.libro')->first();
        $data['remision'] = $remision;
        $data['fecha'] = Carbon::now();
        $data['total_salida'] = NumerosEnLetras::convertir($remision->total);
        $pdf = PDF::loadView('download.pdf.remisiones.nota', $data); 
        
        return $pdf->download('remision-'.$id.'.pdf');
    }

    // GUARDAR COMENTARIO DE LA REMISIÓN
    public function guardar_comentario(Request $request){
        try {
            \DB::beginTransaction();
            $comentario = Comentario::create([
                'remisione_id' => $request->remision_id, 
                'user_id' => auth()->user()->id,
                'comentario' => $request->comentario
            ]);

            $reporte = 'agrego un comentario a la remisión '.$comentario->remisione_id.' / '.$comentario->remisione->cliente->name.' COMENTARIO: '.$comentario->comentario;
            $this->create_report($comentario->remisione_id, $reporte, 'cliente', 'remisiones');

            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        $data = Comentario::whereId($comentario->id)->with('user')->first();
        return response()->json($data);
    }

    public function download_remision($id){
        return Excel::download(new RemisionExport($id), 'remision-'.$id.'.xlsx');
    }

    // Asignar responsable de entregar la remisión
    public function save_responsable(Request $request){
        \DB::beginTransaction();
        try {
            $remision = Remisione::whereId($request->remisione_id)->first();
            $remision->update([
                'responsable' => $request->responsable
            ]);

            $reporte = 'asigno como responsable de la entrega a '.$remision->responsable.' Entrega de la remisión '.$remision->id.' / '.$remision->cliente->name;
            $this->create_report($remision->id, $reporte, 'cliente', 'remisiones');
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }

        return response()->json($remision);
    }

    // Informacion del envio de la remision
    public function save_envio(Request $request){
        $remision = Remisione::whereId($request->remisione_id)->first();
        \DB::beginTransaction();
        try {
            $paqueteria_id = 0;
            $precio = (double) $request->paqueteria['precio'];
            if($precio > 0){
                $id = $request->destinatario['id'];
                if($id == null){
                    $destinatario = Destinatario::create([
                        'destinatario' => strtoupper($request->destinatario['destinatario']), 
                        'rfc' => strtoupper($request->destinatario['rfc']), 
                        'direccion' => strtoupper($request->destinatario['direccion']), 
                        'regimen_fiscal' => $request->destinatario['regimen_fiscal'], 
                        'telefono' => $request->destinatario['telefono']
                    ]);
                    $reporte = 'creo el destinatario '.$destinatario->destinatario;
                    $this->create_report($destinatario->id, $reporte, 'cliente', 'destinatarios');
                } else {
                    $destinatario = Destinatario::find($id);
                }
                $paqueteria = Paqueteria::create([
                    'destinatario_id' => $destinatario->id,
                    'paqueteria' => strtoupper($request->paqueteria['paqueteria']), 
                    'fecha_envio' => $request->paqueteria['fecha_envio'], 
                    'tipo_envio' => $request->paqueteria['tipo_envio'], 
                    'precio' => $precio,
                    'guia' => $request->paqueteria['guia']
                ]);
                $paqueteria_id = $paqueteria->id;

                $reporte = 'registro envió de paquetería en la remisión '.$remision->id.' / '.$remision->cliente->name;
                $this->create_report($paqueteria->id, $reporte, 'cliente', 'paqueterias');
            }

            $remision->update(['paqueteria_id' => $paqueteria_id]);

            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }

        return response()->json($remision);
    }

    public function descargar_gralClientes(){
        return Excel::download(new GAccountExport, 'lista-clientes.xlsx');
    }

    // OBTENER REMISIÓN POR ID
    public function get_remision_id(Request $request){
        $id = $request->id;
        $remision = Remisione::whereId($id)->with(['cliente', 'datos.libro'])->first();
        $clientes = Cliente::orderBy('name', 'asc')->get();
        return response()->json(['remision' => $remision, 'clientes' => $clientes]);
    }

    // GUARDAR DEVOLUCIÓN *CHECK
    public function update(Request $request){
        DB::beginTransaction();
        try {
            $remision = Remisione::find($request->id);
            // OBTENER EL TOTAL Y CASTEAR
            $total = (double) $request->total;
            $cliente_id = $request->cliente['id'];
            
            // ELIMINADOS
            $code_dato_ids = collect();
            $eliminados = collect($request->eliminados);
            $eliminados->map(function($eliminado) use(&$code_dato_ids){
                $dato_id = $eliminado['id'];
                $unidades = (int) $eliminado['unidades'];
                $dato = Dato::find($dato_id);
                $code_dato_ids->push($dato->codes);
    
                // AUMENTAR PIEZAS DE LOS LIBROS ELIMINADOS
                \DB::table('libros')->whereId($eliminado['libro_id'])
                                    ->increment('piezas',  $unidades);
                // ELIMINAR DATOS Y DEVOLUCIONES
                Devolucione::where('dato_id', $dato_id)->delete();
                \DB::table('code_dato')->where('dato_id', $dato_id)->delete();
                $dato->delete();
            });

            $code_dato_ids->map(function($cdi){
                $cdi->map(function($code){
                    $code->update(['estado' => 'inventario']);
                });
            });
            // ** ELIMINADOS
            
            // NUEVOS
            $lista_devoluciones = [];
            $nuevos = collect($request->nuevos);
            $hoy = Carbon::now();
            $nuevos->map(function($nuevo) use (&$lista_devoluciones, $remision, $hoy){
                $libro_id = $nuevo['libro']['id'];
                $unidades = (int) $nuevo['unidades'];
                $total_nuevo = (double) $nuevo['total'];
                $libro = Libro::find($libro_id);

                // CREAR REGISTROS DE DATOS
                $dato = Dato::create([
                    'remisione_id' => $remision->id,
                    'libro_id'  => $libro_id,
                    'costo_unitario' => (float) $nuevo['costo_unitario'],
                    'unidades'  => $unidades,
                    'total'     => $total_nuevo
                ]);

                if($libro->type == 'digital'){
                    $this->get_codes($libro_id, $unidades, $dato->id);
                }

                $lista_devoluciones[] = [
                    'remisione_id' => $remision->id,
                    'dato_id'   => $dato->id,
                    'libro_id' => $libro_id,
                    'unidades_resta' => $unidades,
                    'total_resta' => $total_nuevo,
                    'created_at' => $hoy,
                    'updated_at' => $hoy
                ];

                // DISMINUIR PIEZAS DE LOS LIBROS
                $libro->update(['piezas' => $libro->piezas - $unidades]);
            });

            // CREAR REGISTROS DE DEVOLUCION
            Devolucione::insert($lista_devoluciones);
            // ** NUEVOS

            // EDITADOS
            $e_total_devolucion = 0;
            if(sizeof($request->editados) > 0){
                $editados = collect($request->editados);
                $edatos = collect($request->datos);
                $remision->datos->map(function($d) use(&$editados, &$edatos, &$e_total_devolucion){
                    if($editados->contains($d->id)){
                        $ed = $edatos->firstWhere('id', $d->id);
                        $e_unidades = (int) $ed['unidades'];
                        $e_total = (double) $ed['total'];
                        $e_costo_unitario = (float) $ed['costo_unitario'];
                        if(($e_unidades != $d->unidades) || ($e_costo_unitario != $d->costo_unitario)){
                            if($e_unidades < $d->unidades){
                                // ELIMINAR DATOS Y AGREGAR LIBROS
                                $diferencia = $d->unidades - $e_unidades;
                                if($d->libro->type == 'digital'){
                                    $codes_id = $d->codes()->limit($diferencia)->pluck('codes.id');
                                    Code::whereIn('id', $codes_id)->update([
                                        'estado' => 'inventario'
                                    ]);
                                    \DB::table('code_dato')->where('dato_id', $d->id)
                                            ->whereIn('code_id', $codes_id)->delete();
                                }
                                // AGREGAR PIEZAS DE LOS LIBROS
                                \DB::table('libros')->whereId($d->libro_id)
                                    ->increment('piezas',  $diferencia);
                            }
                            if($e_unidades > $d->unidades){
                                // AGREGAR DATOS Y QUITAR LIBROS
                                $diferencia = $e_unidades - $d->unidades;
                                if($d->libro->type == 'digital'){
                                    $this->get_codes($d->libro_id, $diferencia, $d->id);
                                }

                                // DISMINUIR PIEZAS DE LOS LIBROS
                                \DB::table('libros')->whereId($d->libro_id)
                                    ->decrement('piezas',  $diferencia);
                            }
                            $d->update([
                                'costo_unitario' => $e_costo_unitario,
                                'total' => $e_total,
                                'unidades' => $e_unidades
                            ]);

                            $resta = $e_unidades - $d->devolucione->unidades;
                            $d->devolucione->update([
                                'total' => $d->devolucione->unidades * $e_costo_unitario,
                                'unidades_resta' => $resta,
                                'total_resta' => $resta * $e_costo_unitario
                            ]);

                            $fechas = Fecha::where([
                                'remisione_id' => $d->remisione_id,
                                'libro_id' => $d->libro_id
                            ])->get();
                            $fechas->map(function($fecha) use($e_costo_unitario){
                                $fecha->update([
                                    'total' => $fecha->unidades * $e_costo_unitario
                                ]);
                            });
                        }
                    }
                    $e_total_devolucion += $d->devolucione->total;
                });
            }
            // **EDITADOS

            // MISMO CLIENTE, DIFERENTE TOTAL
            if($cliente_id === $remision->cliente_id){
                // ACTUALIZA LA CUENTA DEL CORTE CORRESPONDIENTE
                $cctotale = $this->get_cctotale($remision, $remision->cliente_id);
                $cct_total = ($cctotale->total - $remision->total) + $total;
                $cct_tdev = ($cctotale->total_devolucion - $remision->total_devolucion) + $e_total_devolucion;
                $cct_tpagar = $cct_total - ($cct_tdev + $cctotale->total_pagos);
                $cctotale->update([
                    'total' => $cct_total,
                    'total_devolucion' => $cct_tdev,
                    'total_pagar' => $cct_tpagar
                ]);

                // BUSCAR EL CLIENTE Y AFECTAR SU CUENTA GENERAL
                $remcliente = Remcliente::where('cliente_id', $remision->cliente_id)->first(); 
                $total_gral = ($remcliente->total - $remision->total) + $total;
                $total_devolucion = ($remcliente->total_devolucion - $remision->total_devolucion) + $e_total_devolucion;
                $total_pagar = $total_gral - ($total_devolucion + $remcliente->total_pagos);
                $remcliente->update([
                    'total' => $total_gral,
                    'total_devolucion' => $total_devolucion,
                    'total_pagar' => $total_pagar
                ]);
            }

            // DIFERENTE CLIENTE
            if($cliente_id !== $remision->cliente_id){
                //*** BUSCAR EL CLIENTE Y AFECTAR SU CUENTA (GENERAL Y CORTE) */
                // 1ro - CUENTA DEL CORTE, AFECTAR SU CUENTA, QUITANDO EL TOTAL DE LA REMISIÓN
                $p_cctotale = $this->get_cctotale($remision, $remision->cliente_id);
                $p_cctotale->update([
                    'total' => $p_cctotale->total - $remision->total,
                    'total_pagar' => $p_cctotale->total_pagar - $remision->total
                ]);

                // Quitarle lo que se le habia asignado de la remisión, pero como se cambio de cliente tiene que quitarse
                $primero = Remcliente::where('cliente_id', $remision->cliente_id)->first(); 
                $primero->update([
                    'total' => $primero->total - $remision->total,
                    'total_pagar' => $primero->total_pagar - $remision->total
                ]);
                //*************** */

                // *** AGREGARLE AL OTRO CLIENTE EL TOTAL */
                // Agregarle lo de la remisión editada, porque se cambio de cliente y ahora a este se le tiene que sumar
                $segundo = Remcliente::where('cliente_id', $cliente_id)->first(); 
                if($segundo === null){
                    Remcliente::create([
                        'cliente_id' => $cliente_id,
                        'total' => $total,
                        'total_pagar' => $total
                    ]);
                } else {
                    $segundo->update([
                        'total' => $segundo->total + $total,
                        'total_pagar' => $segundo->total_pagar + $total
                    ]);
                }
                // 2do CUENTA DEL CORTE
                $s_cctotale = $this->get_cctotale($remision, $cliente_id);
                $s_cctotale->update([
                    'total' => $s_cctotale->total + $total,
                    'total_pagar' => $s_cctotale->total_pagar + $total
                ]);
                //*************** */
            }

            // DIFERENTES DATOS EN LA REMISION
            if($cliente_id !== $remision->cliente_id || $request->fecha_entrega !== $remision->fecha_entrega || $total !== $remision->total){
                // ACTUALIZAR REMISIÓN
                $remision->update([
                    'cliente_id' => $cliente_id,
                    'total' => $total,
                    'total_devolucion' => $e_total_devolucion,
                    'total_pagar' => $total - ($e_total_devolucion + $remision->pagos),
                    'fecha_entrega' => $request->fecha_entrega
                ]);
            } 

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json(true);
    }

    public function get_remcliente(Request $request){
        $cliente_id = $request->cliente_id;
        $remcliente = Remcliente::where('cliente_id', $cliente_id)->first(); 
        return response()->json($remcliente);
    }

    // OBTENER TODAS LAS REMISIONES EN PROCESO O TERMINADAS
    public function pay_remisiones(){
        $remisiones = Remisione::where('total_pagar', '>', '0')
            ->where(function ($query) {
                $query->where('estado', 'Proceso')
                        ->orWhere('estado', 'Terminado');
            })->orderBy('id','desc')
            ->with('cliente')->paginate(20);
        return response()->json($remisiones);
    }

    // NO UTLIZADOS
    public function valores($remisiones, $inicio, $final, $cliente){
        $total_salida = 0;
        $total_devolucion = 0;
        $total_pagos = 0;
        $total_pagar = 0;

        foreach($remisiones as $r){
            if($r->estado != 'Iniciado' && $r->estado != 'Cancelado'){
                $total_salida += $r->total;
                $total_devolucion += $r->total_devolucion;
                $total_pagos += $r->pagos;
                $total_pagar += $r->total_pagar;
            }            
        }

        $data['remisiones'] = $remisiones;
        $data['total_salida'] = $total_salida;
        $data['total_devolucion'] = $total_devolucion;
        $data['total_pagos'] = $total_pagos;
        $data['total_pagar'] = $total_pagar;
        $data['fecha_inicio'] = $inicio;
        $data['fecha_final'] = $final;
        $data['cliente'] = $cliente;

        return $data;
    }

    // CERRAR REMISION
    public function close(Request $request){
        $remision = Remisione::find($request->id);
        \DB::beginTransaction();
        try{ 
            $pagos = $remision->pagos + $remision->total_pagar;
            $remision->update([
                'pagos' => $pagos,
                'total_pagar'   => 0,
                'estado' => 'Terminado',
                'cerrado_por' => auth()->user()->name
            ]);

            $reporte = 'cerro la remisión '.$remision->id.' de '.$remision->cliente->name;
            $this->create_report($remision->id, $reporte, 'cliente', 'remisiones');
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
		}
        
        return response()->json($remision);
    }

    // OBTENER REMISIONES PENDIENTES
    public function obtener_pendientes(){
        $clientes = \DB::table('remisiones')
                        ->join('clientes', 'remisiones.cliente_id', '=', 'clientes.id')
                        ->select('clientes.id', 'clientes.name')
                        ->where('remisiones.estado', 'Proceso')
                        ->orderBy('clientes.name', 'asc')
                        ->groupBy('clientes.id', 'clientes.name')
                        ->get();
        $ref_clientes = [];
        $clientes->map(function($cliente) use(&$ref_clientes){
            $ref_clientes[] = $this->remisiones_proccess($cliente);
        });
        $remisiones = collect($ref_clientes);
        return response()->json($remisiones);
    }

    public function remisiones_proccess($cliente){
        $rs = Remisione::where('estado', 'Proceso')
                        ->where('cliente_id', $cliente->id)
                        ->orderBy('created_at','asc')->get();
        $remcliente = Remcliente::where('cliente_id', $cliente->id)
                                ->first();
        $ds = $this->set_table_cliente_adeudos($rs, $remcliente);
        $remisiones = collect($ds['datos']);
        $datos = [ 
            'cliente_id' => $cliente->id,
            'cliente_name' => $cliente->name,
            'remisiones' => $remisiones,
            'total_pagar' => $remcliente->total_pagar,
            'all_total_pagar' => $ds['total_pagar']
        ];
        
        return $datos;
    }

    // OBTENER PENDIENTES POR CLIENTE
    public function by_cliente_pendientes(Request $request){
        $cliente = Cliente::find($request->cliente_id);
        $datos = $this->remisiones_proccess($cliente);
        return response()->json($datos);
    }

    // OBTENER CONSULTA DE CLIENTES DE ADEUDOS
    public function set_table_cliente_adeudos($remisiones, $remcliente){
        $datos = [];
        $hoy = Carbon::now();
        $total_pagar  = 0;
        $remisiones->map(function($remision) use(&$datos, &$total_pagar, $hoy, $remcliente){
            $diferencia = $remision->created_at->diffInDays($hoy);

            $situacion = '';
            if($remision->total_pagar > $remcliente->total_pagar)
                $situacion = 'Se recomienda cerrar remisión, pero antes revisar libros.';
            
            $data = [
                'id'            => $remision->id,
                'fecha_creacion' => $remision->fecha_creacion,
                // 'cliente'       => $remision->cliente->name,
                // 'total'         => $remision->total,
                // 'pagos'         => $remision->pagos,
                // 'total_devolucion' => $remision->total_devolucion,
                'total_pagar'   => $remision->total_pagar,
                'diferencia'    => $diferencia,
                'situacion'     => $situacion
            ];

            $datos[] = $data;
            $total_pagar += $remision->total_pagar;
        });
        return [
            'datos' => $datos,
            'total_pagar' => $total_pagar
        ];
    }

    // CREAR REMISION
    public function ce_remision($remisione_id, $editar){
        $remision = 0;
        $clientes = Cliente::orderBy('name', 'asc')->get();
        if($editar == 'true' && auth()->user()->role->rol == 'Manager') 
            $remision = Remisione::whereId($remisione_id)->with('cliente', 'datos.libro')->first();

        return view('information.remisiones.ce-remision', compact('remision', 'clientes', 'editar'));
    }

    // OBTENER DETALLES DE LA REMISION
    public function get_details($id){
        $remision = Remisione::whereId($id)
                    ->with([
                        'cliente',
                        'datos.libro',
                        'datos.codes',
                        'depositos',
                        'comentarios.user',
                        'paqueteria.destinatario'
                    ])->withCount('depositos')->first();
        $ds = Devolucione::where('remisione_id', $remision->id)->get();
        $devoluciones = collect();
        $ds->map(function($d) use(&$devoluciones){
            $fs = Fecha::where('remisione_id', $d->remisione_id)
                ->where('libro_id', $d->libro_id)->get();
            $devoluciones->push([
                'id' => $d->id, 
                'remisione_id' => $d->remisione_id, 
                'dato_id' => $d->dato_id, 
                'libro_id' => $d->libro_id,
                'unidades' => $d->unidades, 
                'total' => $d->total,
                'dato' => $d->dato, 
                'libro' => $d->libro,
                'fechas' => $fs
            ]);
        });
        return view('information.remisiones.details-remision', compact('remision', 'devoluciones'));
    }

    public function get_responsables(){
        $responsables = \DB::table('responsables')->orderBy('responsable', 'asc')->get();
        return response()->json($responsables);
    }


    // HISTORIAL DE REMISIONES
    // LISTA DE REMISIONES
    public function lista_remisiones($corte_id){
        return view('information.historial.remisiones', compact('corte_id'));
    }

    public function search_corte_actual(){
        $hoy = Carbon::now();
        $corte = Corte::where('inicio', '<', $hoy)
                        ->where('final', '>', $hoy)
                        ->first();  
        return $corte->id;
    }

    // LISTADO DE REMISIONES POR PERIODO
    public function remisiones_byperiodo(Request $request){
        $corte_id = $request->corte_id;
        if($corte_id == 0){  
            $corte_id = $this->search_corte_actual();
        }

        $remisiones = Remisione::where('corte_id', $corte_id)
                    ->with(['cliente:id,name'])
                    ->orderBy('id','desc')
                    ->paginate(20);
        return response()->json($remisiones);
    }

    // LISTADO DE REMISIONES POR PERIODO Y CLIENTE
    public function byperiodo_cliente(Request $request){
        $corte_id = $request->corte_id;
        $cliente_id = $request->cliente_id;
        
        if($corte_id == 0){  
            $corte_id = $this->search_corte_actual();
        }

        $remisiones = Remisione::where('corte_id', $corte_id)
                    ->where('cliente_id', $cliente_id)
                    ->with(['cliente:id,name'])
                    ->orderBy('id','desc')
                    ->paginate(20);
        return response()->json($remisiones);
    }

    // CREAR REMISION
    public function h_crear_remision(){
        $remision = 0;
        $editar = 'false';
        
        $clientes = Cliente::orderBy('name', 'asc')->get();
        $cortes = Corte::orderBy('inicio', 'asc')->get();
        return view('information.historial.add-edit-remision', compact('remision', 'clientes', 'editar', 'cortes'));
    }

    // VERIFICAR QUE NO EXISTA EL FOLIO INGRESADO
    public function check_folio(Request $request){
        $remision = Remisione::find((int)$request->folio);
        return response()->json($remision);
    } 

    // GUARDAR REMISION
    public function historial_store(Request $request){
        \DB::beginTransaction();
        try {
            $fecha_entrega = $request->fecha_entrega;
            $corte_id = $request->corte_id;
            $total = (double) $request->total;
            $remisione_id = (int) $request->id;

            // CREAR REMISIÓN
            $remision = Remisione::create([
                'id' => $remisione_id,
                'corte_id' => $corte_id,
                'user_id' => auth()->user()->id,
                'cliente_id' => $request->cliente['id'],
                'total' => $total,
                'total_pagar' => $total,
                'fecha_entrega' => $fecha_entrega,
                'estado' => 'Proceso',
                'fecha_creacion' => $fecha_entrega,
                'fecha_devolucion' => $fecha_entrega
            ]);

            // GUARDAR DATOS DE LA REMISION
            $lista_datos = [];
            $request_datos = collect($request->datos);
            $request_datos->map(function($dato) use (&$lista_datos, $remisione_id){
                $lista_datos[] = [
                    'remisione_id' => $remisione_id,
                    'libro_id'  => $dato['libro']['id'],
                    'costo_unitario' => (float) $dato['costo_unitario'],
                    'unidades'  => (int) $dato['unidades'],
                    'total'     => (double) $dato['total']
                    // 'created_at' => $hoy,
                    // 'updated_at' => $hoy
                ];
            });

            Dato::insert($lista_datos);


            // GUARDAR DATOS EN DEVOLUCIONES
            $lista_devoluciones = [];
            $datos = Dato::where('remisione_id', $remisione_id)->get();
            $datos->map(function($dato) use(&$lista_devoluciones){
                $libro_id = $dato->libro_id;
                $lista_devoluciones[] = [
                    'remisione_id' => $dato->remisione_id,
                    'dato_id'   => $dato->id,
                    'libro_id' => $libro_id,
                    'unidades_resta' => $dato->unidades,
                    'total_resta' => $dato->total
                    // 'created_at' => $hoy,
                    // 'updated_at' => $hoy
                ];
            });

            Devolucione::insert($lista_devoluciones);


            // ACTUALIZA LA CUENTA DEL CORTE CORRESPONDIENTE
            $cctotale = $this->get_cctotale($remision, $remision->cliente_id);
            $cctotale->update([
                'total' => $cctotale->total + $remision->total,
                'total_pagar' => $cctotale->total_pagar + $remision->total
            ]);
            
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json();
    }

    // REGISTRAR DEVOLUCIÓN
    public function h_registrar_devolucion($remisione_id){
        $remision = Remisione::whereId($remisione_id)
                        ->with('cliente', 'devoluciones.dato.libro')->first();
        return view('information.historial.registrar-devolucion', compact('remision'));
    }

    public function create_report($id_table, $reporte, $type, $table){
        Reporte::create([
            'user_id' => auth()->user()->id, 
            'type' => $type, 
            'reporte' => $reporte,
            'name_table' => $table, 
            'id_table' => $id_table
        ]);
    }

    // public function enviar(Request $request){
    //     \DB::beginTransaction();
    //     try {
    //         $remision = Remisione::find($request->remision_id);

    //         $entrada = Entrada::on('opuesto')->create([
    //                 'folio' => $remision->id,
    //                 'corte_id' => $remision->corte_id,
    //                 'editorial' => 'MAJESTIC EDUCATION',
    //                 'unidades' => 0,
    //                 'lugar' => 'CMX',
    //                 'tipo' => 'remision',
    //                 'creado_por' => auth()->user()->name,
    //                 'total' => $remision->total
    //             ]);

            
    //         $datos = $remision->datos;
    //         $total_unidades = 0;
            
    //         $datos->map(function($dato) use(&$total_unidades, $entrada){
    //             $unidades = (int) $dato->unidades;
    //             $libro = Libro::on('opuesto')->where('titulo', $dato->libro->titulo)->first();
    //             $libro_id = $libro->id;

    //             $registro = Registro::on('opuesto')->create([
    //                 'entrada_id' => $entrada->id,
    //                 'libro_id'  => $libro_id,
    //                 'unidades'  => $unidades,
    //                 'unidades_que'  => 0,
    //                 'unidades_pendientes'  => $unidades,
    //                 'costo_unitario' => $dato->costo_unitario,
    //                 'total' => $dato->total
    //             ]);

    //             if($libro->type == 'digital') {
    //                 $dato->codes->map(function($c) use($libro_id, $registro){
    //                     $code = Code::on('opuesto')->create([
    //                         'libro_id' => $libro_id, 
    //                         'codigo' => $c->codigo,
    //                         'tipo'  => 'alumno',
    //                         'estado' => 'inventario'
    //                     ]);

    //                     \DB::connection('opuesto')->table('code_registro')
    //                         ->insert([
    //                             'code_id' => $code->id,
    //                             'registro_id' => $registro->id
    //                         ]);
    //                 });

    //             }

    //             // AUMENTAR PIEZAS DE LOS LIBROS AGREGADOS
    //             $libro->update(['piezas' => $libro->piezas + $unidades]);

    //             $total_unidades += $unidades;
    //         });

    //         $entrada->update(['unidades' => $total_unidades]);
            
    //         $editoriale = Editoriale::on('opuesto')->where('editorial', 'MAJESTIC EDUCATION')->first();
    //         $ectotale = Ectotale::on('opuesto')->where([
    //                     'corte_id' => $entrada->corte_id, // CAMBIAR POR ENTRADA
    //                     'editoriale_id' => $editoriale->id
    //                 ])->first();
    //         $ectotale->update([
    //             'total' => $ectotale->total + $entrada->total,
    //             'total_pagar' => $ectotale->total_pagar + $entrada->total
    //         ]);
    //         $enteditoriale = Enteditoriale::on('opuesto')->where('editorial', $editoriale->editorial)->first();
    //         $enteditoriale->update([
    //             'total' => $enteditoriale->total + $entrada->total,
    //             'total_pendiente' => $enteditoriale->total_pendiente + $entrada->total
    //         ]);

    //         $remision->update(['envio' => true]);

    //         \DB::commit();
    //     } catch (Exception $e) {
    //         \DB::rollBack();
    //         return response()->json($exception->getMessage());
    //     }
    //     return response()->json();
    // }
    
}
