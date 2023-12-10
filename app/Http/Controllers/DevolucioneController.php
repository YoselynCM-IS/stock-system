<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use App\Devolucione;
use App\Defectuoso;
use App\Remcliente;
use Carbon\Carbon;
use App\Remisione;
use App\Cctotale;
use App\Vendido;
use App\Reporte;
use App\Cliente;
use App\Libro;
use App\Fecha;
use App\Dato;
use App\Pack;

class DevolucioneController extends Controller
{
    // GUARDAR DEVOLUCIÓN DE REMISIÓN
    // Función utilizada en DevolucionController
    public function update(Request $request){
        try {
            \DB::beginTransaction();
            // Buscar remisión
            $remision = Remisione::whereId($request->id)->first();
            $remcliente = Remcliente::where('cliente_id', $remision->cliente_id)->first();

            $entregado_por = $request->entregado_por;
            $total_devolucion = 0;
            
            // DEVOLUCIONES
            $scratchs = collect();
            $devoluciones = collect($request->devoluciones);
            $hoy = Carbon::now();
            $devoluciones->map(function($devolucion) use(&$scratchs, $remision, $entregado_por, &$total_devolucion, $hoy){
                $unidades_base = (int) $devolucion['unidades_base'];
                $total_base = (double) $devolucion['total_base'];
                $defectuosos = (int) $devolucion['defectuosos'];
                $comentario = $devolucion['comentario'];

                if($unidades_base != 0){
                    // Buscar devolución
                    $d = Devolucione::find($devolucion['id']);
                    // Crear registros de fecha de la devolución
                    $fecha = Fecha::create([
                        'remisione_id' => $remision->id,
                        'fecha_devolucion' => $hoy->format('Y-m-d'),
                        'libro_id' => $d->libro->id,
                        'unidades' => $unidades_base,
                        'defectuosos' => $defectuosos,
                        'comentario' => $comentario,
                        'total' => $total_base,
                        'entregado_por' => $entregado_por,
                        'creado_por' => auth()->user()->name,
                        'created_at' => $hoy,
                        'updated_at' => $hoy
                    ]);

                    $reporte = 'registro la devolución (remision) de '.$unidades_base.' unidades - '.$d->libro->editorial.': '.$d->libro->type.' '.$d->libro->ISBN.' / '.$d->libro->titulo.' para '.$d->remisione_id.' / '.$d->remisione->cliente->name;
                    $this->create_report($fecha->id, $reporte, 'libro', 'fechas');
                    
                    $unidades = $d->unidades + $unidades_base;
                    $total = $d->total + $total_base;
                    $unidades_resta = $d->unidades_resta - $unidades_base;
                    $total_resta = $d->total_resta - $total_base;
                    // Actualizar la tabla de devolución
                    $d->update([
                        'unidades' => $unidades, 
                        'unidades_resta' => $unidades_resta,
                        'total' => $total,
                        'total_resta' => $total_resta
                    ]);

                    // AUMENTAR PIEZAS DE LOS LIBROS DEVUELTOS
                    $l = Libro::whereId($d->libro->id)->first();
                    $l->update([
                        'piezas' => $l->piezas + ($unidades_base - $defectuosos),
                        'defectuosos' => $l->defectuosos + $defectuosos
                    ]);
                    if($defectuosos > 0){
                        Defectuoso::create([
                            'libro_id' => $d->libro->id, 
                            'numero' => $defectuosos, 
                            'comentario' => auth()->user()->name.' / Devolución: '.$comentario
                        ]);
                    }

                    // DEVOLUCION DE CODIGOS
                    $codes = $d->dato->codes()->whereIn('code_id', $devolucion['code_dato'])->get();
                    $codes->map(function($code){
                        $code->update(['estado' => 'inventario']);
                        $code->datos()
                            ->updateExistingPivot($code->pivot->dato_id, [
                                'devolucion' => true
                            ]);
                    });
                    
                    if(($devolucion['scratch'] && $unidades_base > 0) || ($d->libro->type == 'digital' && $d->dato->pack_id != null)){
                        $scratchs->push([
                            'fecha_id' => $fecha->id,
                            'libro_digital' => $devolucion['libro_id'],
                            'unidades' => $unidades_base,
                        ]);
                    }
                } 

                $total_devolucion += $total_base;
            });
            
            $scratchs->map(function($scratch) use($devoluciones){
                $p = Pack::where('libro_digital', $scratch['libro_digital'])
                            ->whereIn('libro_fisico', $devoluciones->pluck('libro_id'))->first();
                Fecha::whereId($scratch['fecha_id'])->update(['pack_id' => $p->id]);
                $p->update(['piezas' => $p->piezas + $scratch['unidades']]);
            });

            $total_pagar = $remision->total_pagar - $total_devolucion;
            $t_devolucion = $remision->total_devolucion + $total_devolucion;
            
            // ACTUALIZAR REMISION
            $remision->update([
                'total_devolucion' => $t_devolucion,
                'total_pagar'   => $total_pagar
            ]);
            if ((int) $total_pagar === 0) {
                if ($remision->depositos->count() > 0)
                    $this->restantes_to_cero($remision);
                $remision->update(['estado' => 'Terminado']); 
            }

            // ACTUALIZA LA CUENTA DEL CORTE CORRESPONDIENTE
            $cctotale = Cctotale::where([
                'cliente_id' => $remision->cliente_id,
                'corte_id'  => $remision->corte_id
            ])->first();
            $cctotale->update([
                'total_devolucion' => $cctotale->total_devolucion + $total_devolucion,
                'total_pagar' => $cctotale->total_pagar - $total_devolucion
            ]);
            
            // ACTUALIZAR CUENTA GENERAL DEL CLIENTE
            $remcliente->update([
                'total_devolucion' => $remcliente->total_devolucion + $total_devolucion,
                'total_pagar' => $remcliente->total_pagar - $total_devolucion
            ]);

            $reporte = 'registro la devolución de la remisión '.$remision->id.' de '.$remision->cliente->name;
            $this->create_report($remision->id, $reporte, 'cliente', 'fechas');

            \DB::commit();

        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json($remision);
    } 

    public function create_report($id_table, $reporte, $type, $name_table){
        Reporte::create([
            'user_id' => auth()->user()->id, 
            'type' => $type, 
            'reporte' => $reporte,
            'name_table' => $name_table, 
            'id_table' => $id_table
        ]);
    }

    // ACTUALIZAR LAS UNIDADES RESTANTES DE LAS REMISIONES
    // SOLO SI EN LA REMISIÓN SE REALIZO UN DEPOSITO
    public function restantes_to_cero($remision) {
        Devolucione::where('remisione_id', $remision->id)->update([
            'unidades_resta' => 0,
            'total_resta' => 0
        ]);
    }
    
    //Mostrar todas las devoluciones (ELIMINADA)
    public function all_devoluciones(){
        $remisiones = Remisione::where('total_pagar', '>', '0')
                                ->where(function ($query) {
                                    $query->where('estado', 'Proceso')
                                            ->orWhere('estado', 'Terminado');
                                })->orderBy('id','desc')
                                ->with('cliente')->get(); 
        return response()->json($remisiones);
    }

    // HISTORIAL DE REMISIONES
    // GUARDAR DEVOLUCIÓN DE REMISIÓN
    public function historial_update(Request $request){
        try {
            \DB::beginTransaction();
            // Buscar remisión
            $remision = Remisione::whereId($request->remisione_id)->first();
            $total_devolucion = 0;
            
            // DEVOLUCIONES
            $lista_fechas = [];
            $devoluciones = collect($request->devoluciones);
            $devoluciones->map(function($devolucion) use(&$lista_fechas, $remision, $request, &$total_devolucion){
                $unidades_base = (int)$devolucion['unidades_base'];
                $total_base = $devolucion['total_base'];
                if($unidades_base != 0){
                    // Buscar devolución
                    $d = Devolucione::find($devolucion['devolucion_id']);
                    $lista_fechas[] = [
                        'remisione_id' => $remision->id,
                        'fecha_devolucion' => $request->fecha_devolucion,
                        'libro_id' => $d->libro_id,
                        'unidades' => $unidades_base,
                        'total' => $total_base,
                        'entregado_por' => $request->entregado_por,
                        'creado_por' => auth()->user()->name
                        // 'created_at' => $hoy,
                        // 'updated_at' => $hoy
                    ];
                    
                    $unidades = $d->unidades + $unidades_base;
                    $total = $d->total + $total_base;
                    $unidades_resta = $d->unidades_resta - $unidades_base;
                    $total_resta = $d->total_resta - $total_base;
                    // Actualizar la tabla de devolución
                    $d->update([
                        'unidades' => $unidades, 
                        'unidades_resta' => $unidades_resta,
                        'total' => $total,
                        'total_resta' => $total_resta
                    ]);
                } 
                $total_devolucion += $total_base;
            });

            // Crear registros de fecha de la devolución
            Fecha::insert($lista_fechas);
            
            $total_pagar = $remision->total_pagar - $total_devolucion;
            $t_devolucion = $remision->total_devolucion + $total_devolucion;
            
            // ACTUALIZAR REMISION
            $remision->update([
                'total_devolucion' => $t_devolucion,
                'total_pagar'   => $total_pagar
            ]);
            if ((int) $total_pagar === 0) {
                if ($remision->depositos->count() > 0)
                    $this->restantes_to_cero($remision);
                $remision->update(['estado' => 'Terminado']); 
            }

            // ACTUALIZA LA CUENTA DEL CORTE CORRESPONDIENTE
            $cctotale = Cctotale::where([
                'cliente_id' => $remision->cliente_id,
                'corte_id'  => $remision->corte_id
            ])->first();
            $cctotale->update([
                'total_devolucion' => $cctotale->total_devolucion + $total_devolucion,
                'total_pagar' => $cctotale->total_pagar - $total_devolucion
            ]);

            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json();
    }  
}
