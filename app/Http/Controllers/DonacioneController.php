<?php

namespace App\Http\Controllers;

use App\Exports\DonacionesExport;
use App\Exports\donaciones\DonacionExport;
use Illuminate\Http\Request;
use App\Donacione;
use Carbon\Carbon;
use App\Reporte;
use App\Regalo;
use App\Libro;
use App\Code;
use Excel;
use PDF;

class DonacioneController extends Controller
{
    // OBTENER TODAS LAS DONACIONES
    public function index(){
        $regalos = Regalo::orderBy('id','desc')->paginate(20);
        return response()->json($regalos);
    }

    // GUARDAR UNA DONACIÓN *CHECK
    // Función utilizada en DevoluciónController
    public function store(Request $request) {
        \DB::beginTransaction();
        try{
            $regalo = Regalo::create([
                'cliente_id' => $request->cliente_id,
                'plantel' => $request->plantel,
                'descripcion' => strtoupper($request->descripcion),
                'unidades' => (int) $request->unidades,
                'entregado_por' => null,
                'creado_por' => auth()->user()->name
            ]);

            $lista_codes = collect();
            $donaciones = collect($request->donaciones);
            $hoy = Carbon::now();

            $donaciones->map(function($donacion) use($regalo, $hoy, &$lista_codes){
                $unidades = $donacion['unidades'];
                $libro_id = $donacion['id'];

                // Crear registros de donación
                $d = Donacione::create([
                    'regalo_id' => $regalo->id,
                    'libro_id' => $libro_id,
                    'unidades' => $unidades,
                    'created_at' => $hoy,
                    'updated_at' => $hoy
                ]);

                if($d->libro->type == 'digital'){
                    $lista_codes->push([
                        'donacione_id'   => $d->id,
                        'libro_id'  => $d->libro_id,
                        'unidades'  => $d->unidades
                    ]);
                }

                // DISMINUIR PIEZAS DE LOS LIBROS
                \DB::table('libros')->whereId($libro_id)
                    ->decrement('piezas', $unidades);

                $reporte = 'registro la salida (donación) de '.$d->unidades.' unidades - '.$d->libro->editorial.': '.$d->libro->type.' '.$d->libro->ISBN.' / '.$d->libro->titulo.' para '.$regalo->plantel;
                $this->create_report($d->id, $reporte, 'libro', 'donaciones');
            });

            $lista_codes->map(function($lc){
                $codes = Code::where('libro_id', $lc['libro_id'])
                                ->where('estado', 'inventario')
                                ->where('tipo', 'alumno')
                                ->orderBy('created_at', 'asc')
                                ->limit($lc['unidades'])
                                ->get();
                
                $code_donacione = [];
                $codes->map(function($code) use (&$code_donacione){
                    $code_donacione[] = $code->id;
                    $code->update(['estado' => 'ocupado']);
                });

                $donacione = Donacione::find($lc['donacione_id']);
                $donacione->codes()->sync($code_donacione);
            });
            
            $reporte = 'creo la donación para '.$regalo->plantel;
            $this->create_report($regalo->id, $reporte, 'cliente', 'regalos');
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
        }
        
        return response()->json($regalo);
    }

    public function detalles_donacion(Request $request){
        $regalo_id = $request->regalo_id;
        $regalo = Regalo::whereId($regalo_id)->with('donaciones.libro', 'donaciones.codes')->first();
        return response()->json($regalo);
    }

    public function by_plantel(Request $request){
        $queryPlantel = $request->queryPlantel;
        $regalos = Regalo::where('plantel','like','%'.$queryPlantel.'%')
                    ->orderBy('id','desc')->paginate(20);
        return response()->json($regalos);
    }

    public function by_fecha(Request $request){
        $inicio = $request->inicio;
        $final = $request->final;
        $plantel = $request->plantel;

        $fechas = $this->format_date($inicio, $final);
        $fecha1 = $fechas['inicio'];
        $fecha2 = $fechas['final'];

        if($plantel === null){
            $regalos = Regalo::whereBetween('created_at', [$fecha1, $fecha2])
                                ->orderBy('id','desc')->paginate(20);
        } else {
            $regalos = Regalo::where('plantel','like','%'.$plantel.'%')
                                ->whereBetween('created_at', [$fecha1, $fecha2])
                                ->orderBy('id','desc')->paginate(20);
        }
        
        return response()->json($regalos);
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

    public function download_donacion($plantel, $inicio, $final, $tipo){
        return Excel::download(new DonacionesExport($plantel, $inicio, $final, $tipo), 'reporte-donaciones.xlsx');
    }

    public function entrega_donacion(Request $request){
        $regalo = Regalo::whereId($request->id)->first();
        \DB::beginTransaction();
        try {
            $regalo->update(['entregado_por' => $request->entregado_por]);

            $reporte = 'asigno como responsable a '.$regalo->entregado_por.' Entrega de la donación '.$regalo->plantel;
            $this->create_report($regalo->id, $reporte, 'cliente', 'regalos');

            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json($regalo);
    }

    public function download_regalo($id){
        return Excel::download(new DonacionExport($id), 'nota-donacion.xlsx');
    }

    public function create_report($regalo_id, $reporte, $type, $name_table){
        Reporte::create([
            'user_id' => auth()->user()->id, 
            'type' => $type, 
            'reporte' => $reporte,
            'name_table' => $name_table, 
            'id_table' => $regalo_id
        ]);
    }
}
