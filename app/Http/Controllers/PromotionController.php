<?php

namespace App\Http\Controllers;

use App\Exports\PromotionsExport;
use App\Exports\promociones\PromotionExport;
use Illuminate\Http\Request;
use App\Prodevolucione;
use App\Promotion;
use App\Departure;
use Carbon\Carbon;
use App\Reporte;
use App\Libro;
use App\Code;
use Excel;
use PDF;
// use App\Enteditoriale;
// use App\Editoriale;
// use App\Registro;
// use App\Ectotale;
// use App\Entrada;
// use App\Corte;

class PromotionController extends Controller
{

    // OBTENER TODAS LAS PROMOCIONES PAGINADAS
    public function index(){
        $promotions = Promotion::with('departures')->orderBy('folio','desc')
                        ->paginate(20);
        return response()->json($promotions);
    }

    // MOSTRAR PROMOCIONES POR FOLIO
    // Función utilizada en PromocionesComponent
    public function buscar_folio(Request $request){
        $folio = $request->folio;
        $promotion = Promotion::where('folio', $folio)->first();
        return response()->json($promotion);
    }

    // MOSTRAR PROMOCIONES POR PLANTEL
    // Función utilizada en PromocionesComponent
    public function buscar_plantel(Request $request){
        $queryPlantel = $request->queryPlantel;
        $promotions = Promotion::where('plantel','like','%'.$queryPlantel.'%')
                ->orderBy('folio','desc')->paginate(20);
        return response()->json($promotions);
    }

    // MOSTRAR LOS DETALLES DE UNA PROMOCIÓN
    // Función utilizada en PromocionesComponent
    public function obtener_departures(Request $request){
        $promotion_id = $request->promotion_id;
        $promotion = Promotion::whereId($promotion_id)
            ->with('departures.libro', 'prodevoluciones.libro', 'departures.codes')->first();
        // $departures = Departure::where('promotion_id', $promotion_id)->with('libro')->get();
        return response()->json($promotion);
    }

    // GUARDAR UNA PROMOCIÓN
    // Función utilizada en PromocionesComponent
    public function store(Request $request){
        try{
            \DB::beginTransaction();
            $ultimo = Promotion::all()->last();
            $actual = (int)(substr($ultimo->folio, -4)) + 1;
            $w_zeros = str_pad($actual, 4, '0',STR_PAD_LEFT);
            
            $promotion = Promotion::create([
                'cliente_id' => $request->cliente_id,
                'folio' => 'A-P'.$w_zeros,
                'plantel' => strtoupper($request->plantel),
                'descripcion' => strtoupper($request->descripcion),
                'entregado_por' => $request->entregado_por,
                'creado_por' => auth()->user()->name
            ]);

            $lista_codes = collect();
            $unidades = 0;
            $departures = collect($request->departures);
            $departures->map(function($departure) use(&$lista_codes, $promotion, &$unidades){
                $u = (int) $departure['unidades'];
                $libro_id = $departure['id'];
                $type = $departure['type'];
                $d = Departure::create([
                    'promotion_id' => $promotion->id,
                    'libro_id' => $libro_id,
                    'unidades' => $u,
                    'unidades_pendientes' => $u
                ]);
                
                $libro = Libro::whereId($libro_id)->first();
                if($type != 'digital'){
                    $libro->update(['piezas' => $libro->piezas - $u]);
                }
                if($type == 'digital'){
                    $lista_codes->push([
                        'departure_id'   => $d->id,
                        'libro_id'  => $libro->id,
                        'tipo' => $departure['tipo'],
                        'unidades'  => $u
                    ]);
                }
                
                $unidades += $u;
                $reporte = 'registro la salida (promoción) de '.$d->unidades.' unidades - '.$libro->editorial.': '.$libro->type.' '.$libro->ISBN.' / '.$libro->titulo.' para '.$d->promotion->folio.' / '.$d->promotion->plantel;
                $this->create_report($d->id, $reporte, 'libro', 'departures');
            });

            $lista_codes->map(function($lc) {
                $codes = Code::where('libro_id', $lc['libro_id'])
                                ->where('estado', 'inventario')
                                ->where('tipo', $lc['tipo'])
                                ->orderBy('created_at', 'asc')
                                ->limit($lc['unidades'])
                                ->get();

                $code_departure = [];
                $codes->map(function($code) use (&$code_departure){
                    $code_departure[] = $code->id;
                    $code->update(['estado' => 'ocupado']);
                });
    
                $departure = Departure::find($lc['departure_id']);
                $departure->codes()->sync($code_departure);
            });
            
            $promotion->update([
                'unidades' => $unidades,
                'unidades_pendientes' => $unidades
            ]);

            $reporte = 'creo la promoción '.$promotion->folio.' para '.$promotion->plantel;
            $this->create_report($promotion->id, $reporte, 'cliente', 'promotions');

            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
        }
        return response()->json($promotion);
    }

    // BUSCAR PROMOCIÓN POR FECHAS
    public function buscar_fecha_promo(Request $request){
        $inicio = $request->inicio;
        $final = $request->final;
        $plantel = $request->plantel;

        $fechas = $this->format_date($inicio, $final);
        $fecha1 = $fechas['inicio'];
        $fecha2 = $fechas['final'];

        if($plantel === null){
            $promotions = Promotion::whereBetween('created_at', [$fecha1, $fecha2])
                                ->orderBy('folio','desc')->paginate(20);
        } else {
            $promotions = Promotion::where('plantel','like','%'.$plantel.'%')
                                ->whereBetween('created_at', [$fecha1, $fecha2])
                                ->orderBy('folio','desc')->paginate(20);
        }
        
        return response()->json($promotions);
    }

    public function format_date($fecha1, $fecha2){
        $inicio = new Carbon($fecha1);
        $final 	= new Carbon($fecha2);
        $inicio = $inicio->format('Y-m-d 00:00:00');
        $final 	= $final->format('Y-m-d 23:59:59');

        $fechas = [
            'inicio' => $inicio,
            'final' => $final
        ];

        return $fechas;
    }

    // DESCARGAR REPORTE
    public function download_promotion($plantel, $inicio, $final, $tipo){
        return Excel::download(new PromotionsExport($plantel, $inicio, $final, $tipo), 'reporte-promociones.xlsx');
    }

    public function download_promocion($id){
        $promocion = Promotion::find($id);
        $name_archivo = 'promocion_' . $promocion->folio . '.xlsx';
        return Excel::download(new PromotionExport($promocion->id), $name_archivo);
    }

    public function cancel(Request $request){
        try{
            \DB::beginTransaction();
            $promotion = Promotion::find($request->promotion_id);

            $promotion->departures->map(function($departure){
                if($departure->libro->type != 'digital'){
                    \DB::table('libros')->whereId($departure->libro_id)->increment('piezas', $departure->unidades);
                } 
                if($departure->libro->type == 'digital'){
                    // BORRAR CODIGOS
                    $departure->codes->map(function($code){
                        $code->update(['estado' => 'inventario']);
                    });
                    $departure->codes()->detach();
                }
                
                $reporte = 'registro la cancelación (promoción) de '.$departure->unidades.' unidades - '.$departure->libro->editorial.': '.$departure->libro->type.' '.$departure->libro->ISBN.' / '.$departure->libro->titulo.' para '.$departure->promotion->folio.' / '.$departure->promotion->plantel;
                $this->create_report($departure->id, $reporte, 'libro', 'departures');
            });

            $promotion->update([
                'estado' => 'Cancelado',
                'unidades_pendientes' => 0
            ]);

            $reporte = 'cancelo la promoción '.$promotion->folio.' de '.$promotion->plantel;
            $this->create_report($promotion->id, $reporte, 'cliente', 'promotions');

            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
        }
        return response()->json(true);
    }

    // GUARDAR DEVOLUCION *CHECK
    public function devolucion(Request $request){
        try{
            \DB::beginTransaction();

            $devoluciones = collect($request->departures);
            $devoluciones->map(function($d){
                $unidades_devolucion = (int) $d['unidades'];

                if($unidades_devolucion > 0){
                    $departure = Departure::find($d['departure_id']);
                    $libro_id = $departure->libro_id;

                    $pd = Prodevolucione::create([
                        'promotion_id' => $departure->promotion_id, 
                        'libro_id' => $libro_id, 
                        'unidades' => $unidades_devolucion,
                        'creado_por' => auth()->user()->name
                    ]);

                    // ACTUALIZAR UNIDADES PENDIENTES DE ESE LIBRO EN ESA PROMOCION
                    $departure->update([
                        'unidades_pendientes' => $departure->unidades_pendientes - $unidades_devolucion
                    ]);

                    // AUMENTAR PIEZAS DE LOS LIBROS DEVUELTOS
                    \DB::table('libros')->whereId($libro_id)
                        ->increment('piezas', $unidades_devolucion); 

                    $reporte = 'registro la devolución (promoción) de '.$pd->unidades.' unidades - '.$departure->libro->editorial.': '.$departure->libro->type.' '.$departure->libro->ISBN.' / '.$departure->libro->titulo.' para '.$departure->promotion->folio.' / '.$departure->promotion->plantel;
                    $this->create_report($pd->id, $reporte, 'libro', 'prodevoluciones');
                }
            });
            
            $promotion = Promotion::find($request->id);
            $unidades_devolucion = $promotion->unidades_devolucion + (int)$request->unidades_devolucion;
            $unidades_pendientes = $promotion->unidades - $unidades_devolucion;
            $promotion->update([
                'unidades_devolucion' => $unidades_devolucion,
                'unidades_pendientes' => $unidades_pendientes
            ]);

            $reporte = 'registro la devolución de la promoción '.$promotion->folio.' de '.$promotion->plantel;
            $this->create_report($promotion->id, $reporte, 'cliente', 'prodevoluciones');

            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
        }
        return response()->json(true);
    }

    public function create_report($promotion_id, $reporte, $type, $name_table){
        Reporte::create([
            'user_id' => auth()->user()->id, 
            'type' => $type, 
            'reporte' => $reporte,
            'name_table' => $name_table,
            'id_table' => $promotion_id
        ]);
    }

    // public function enviar(Request $request){
    //     \DB::beginTransaction();
    //     try {
    //         $promotion = Promotion::find($request->promotion_id);
    //         $hoy = Carbon::now();
    //         $corte = Corte::where('inicio', '<', $hoy)
    //                     ->where('final', '>', $hoy)
    //                     ->first();
    //         $entrada = Entrada::on('opuesto')->create([
    //             'folio' => $promotion->folio,
    //             'corte_id' => $corte->id,
    //             'editorial' => 'MAJESTIC EDUCATION',
    //             'unidades' => 0,
    //             'lugar' => 'CMX',
    //             'tipo' => 'promocion',
    //             'creado_por' => auth()->user()->name,
    //             'unidades' => $promotion->unidades,
    //             'total' => 0
    //         ]);
            
    //         $departures = $promotion->departures;
    //         $departures->map(function($departure) use($entrada){
    //             $unidades = (int) $departure->unidades;
    //             $libro = Libro::on('opuesto')->where('titulo', $departure->libro->titulo)->first();
    //             $libro_id = $libro->id;
                
    //             $registro = Registro::on('opuesto')->create([
    //                 'entrada_id' => $entrada->id,
    //                 'libro_id'  => $libro_id,
    //                 'unidades'  => $unidades,
    //                 'unidades_que'  => 0,
    //                 'unidades_pendientes'  => $unidades,
    //                 'costo_unitario' => 0,
    //                 'total' => 0
    //             ]);
                
    //             if($libro->type == 'digital') {
    //                 $departure->codes->map(function($c) use($libro_id, $registro){
    //                     $code = Code::on('opuesto')->create([
    //                         'libro_id' => $libro_id, 
    //                         'codigo' => $c->codigo,
    //                         'tipo'  => $c->tipo,
    //                         'estado' => 'inventario'
    //                     ]);
                        
    //                     \DB::connection('opuesto')->table('code_registro')
    //                         ->insert([
    //                             'code_id' => $code->id,
    //                             'registro_id' => $registro->id
    //                         ]);
    //                 });

    //             }
            
    //             if($libro->type != 'digital'){
    //                 // AUMENTAR PIEZAS DE LOS LIBROS AGREGADOS
    //                 $libro->update(['piezas' => $libro->piezas + $unidades]);
    //             }
    //         });

    //         $promotion->update(['envio' => true]);

    //         \DB::commit();
    //     } catch (Exception $e) {
    //         \DB::rollBack();
    //         return response()->json($exception->getMessage());
    //     }
    //     return response()->json();
    // }
}
