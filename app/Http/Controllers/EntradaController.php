<?php

namespace App\Http\Controllers;

use Intervention\Image\ImageManagerStatic as Image;
use Spatie\Dropbox\Client as ClienteDropbox;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Exports\EntradasExport;
use App\Exports\entradas\EntradaExport;
use App\Exports\EAccountExport;
use Illuminate\Http\Request;
use App\Entdevolucione;
use App\Enteditoriale;
use App\Comprobante;
use App\Entdeposito;
use App\Editoriale;
use Carbon\Carbon;
use App\Repayment;
use App\Registro;
use App\Ectotale;
use App\Imprenta;
use App\Entrada;
use App\Reporte;
use App\Salida;
use App\Libro;
use App\Corte;
use App\Code;
use App\Pack;
use Excel;
use PDF;

class EntradaController extends Controller
{
    // OBTENER LA LISTA DE ENTRADAS
    public function lista(){
        return view('information.entradas.lista');
    }

    // OBTENER TODAS LAS ENTRADAS
    public function index(){
        $entradas = Entrada::with('registros')
                        ->orderBy('id','desc')->paginate(20);
        return response()->json($entradas);
    }

    // OBTENER LA VISTA PARA LOS PAGOS DE PROVEEDORES
    // PAGOS DE ENTRADAS
    public function pagos(){
        $editoriales = Enteditoriale::orderBy('editorial', 'asc')
                        ->withCount('entdepositos')->get();
        return view('information.entradas.pagos', compact('editoriales'));
    }

    // GUARDAR ENTRADA DE LIBROS FISICOS *CHECK
    // Función utilizada en EntradasComponent
    public function store(Request $request){
        \DB::beginTransaction();
        try {
            $editorial = $request->editorial;
            $folio = strtoupper($request->folio);
            $lugar = 'CMX';
            if($request->queretaro == 'true') $lugar = 'DOS';

            if($editorial == 'MAJESTIC EDUCATION') $imprenta_id = $request->imprenta_id; 
            else $imprenta_id = null;
            
            $corte = $this->get_corte();
            $entrada = Entrada::create([
                'folio' => $folio,
                'corte_id' => $corte->id,
                'editorial' => $editorial,
                'imprenta_id' => $imprenta_id,
                'unidades' => $request->unidades,
                'lugar' => $lugar,
                'creado_por' => auth()->user()->name
            ]);

            $rs = json_decode($request->registros);
            $unidades = 0;
            $registros = collect($rs);
            $hoy = Carbon::now();
            $registros->map(function($item) use($entrada, &$unidades, $hoy){
                $unidades_base = (int) $item->unidades;
                $libro_id = $item->id;
                // CREAR LISTA DE REGISTROS
                $registro = Registro::create([
                    'entrada_id' => $entrada->id,
                    'pack_id' => $item->pack_id,
                    'libro_id'  => $libro_id,
                    'unidades'  => $unidades_base,
                    'unidades_que'  => $item->unidades_que,
                    'unidades_pendientes'  => $unidades_base,
                    'created_at' => $hoy,
                    'updated_at' => $hoy
                ]);

                $reporte = 'registro la entrada (entrada) de '.$registro->unidades.' unidades - '.$registro->libro->editorial.': '.$registro->libro->type.' '.$registro->libro->ISBN.' / '.$registro->libro->titulo.' para '.$entrada->folio.' / '.$entrada->editorial;
                $this->create_report($registro->id, $reporte, 'libro', 'registros');

                // AUMENTAR PIEZAS DE LOS LIBROS AGREGADOS
                \DB::table('libros')->whereId($libro_id)
                    ->increment('piezas', $unidades_base);   
                
                $unidades += $unidades_base;
            });

            // AUMENTAR PIEZAS EN PACK
            $ps = json_decode($request->packs);
            $packs = collect($ps);
            $packs->map(function($pack){
                \DB::table('packs')->whereId($pack->id)
                                    ->increment('piezas',  (int) $pack->unidades);
            });
            
            $entrada->update(['unidades' => $unidades]);
            // *** SUBIR COMPROBANTE
            $files = $request->file('files');
            foreach($files as $file) {
                $this->upload_comprobante($file, $entrada);
            }
            // *** SUBIR COMPROBANTE
            $get_entrada = Entrada::whereId($entrada->id)->first();

            $reporte = 'creo la entrada '.$entrada->folio.' de '.$entrada->editorial;
            $this->create_report($entrada->id, $reporte, 'proveedor', 'entradas');

            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json($get_entrada);
    }

    public function upload_image($request, $name_file, $ruta){
        // $image = Image::make($request->file('file'));
        // $image->resize(1280, null, function ($constraint) {
        //     $constraint->aspectRatio();
        //     $constraint->upsize();
        // });
    }

    // GUARDAR ENTRADA DE CODIGOS *CHECK
    public function store_codes(Request $request){
        \DB::beginTransaction();
        try {
            $editorial = $request->editorial;
            $lugar = 'CMX';
            $folio = strtoupper($request->folio);
            $corte = $this->get_corte();
            $imprenta_id = null;

            if($request->imprenta_id != 'null') $imprenta_id = $request->imprenta_id;

            $entrada = null;
            $entrada = Entrada::create([
                'folio' => $folio,
                'imprenta_id' => $imprenta_id,
                'corte_id' => $corte->id,
                'editorial' => $editorial,
                'unidades' => $request->unidades,
                'lugar' => $lugar,
                'creado_por' => auth()->user()->name
            ]);

            $libros = collect(json_decode($request->libros));
            $hoy = Carbon::now();

            $l = collect();
            $libros->map(function($item) use($entrada, $hoy, &$l){
                $unidades_base = (int) $item->unidades;
                $libro_id = $item->libro_id;
                
                $registro = Registro::create([
                    'entrada_id' => $entrada->id,
                    'libro_id'  => $libro_id,
                    'unidades'  => $unidades_base,
                    'unidades_que'  => 0,
                    'unidades_pendientes'  => $unidades_base
                ]);

                $reporte = 'registro la entrada (entrada) de '.$registro->unidades.' códigos - '.$registro->libro->editorial.': '.$registro->libro->type.' '.$registro->libro->ISBN.' / '.$registro->libro->titulo.' para '.$entrada->folio.' / '.$entrada->editorial;
                $this->create_report($registro->id, $reporte, 'libro', 'registros');

                $codes = collect($item->codes);
                $code_registro = [];
                $codes->map(function($code) use (&$l, &$code_registro){
                    Code::whereId($code->id)->update(['estado' => 'inventario']);
                    $code_registro[] = $code->id;
                });

                $registro->codes()->sync($code_registro);
    
                if($item->tipo == 'alumno'){
                    // AUMENTAR PIEZAS DE LOS LIBROS AGREGADOS
                    \DB::table('libros')->whereId($libro_id)
                        ->increment('piezas', $unidades_base);
                }
            });

            // *** SUBIR COMPROBANTE
            $file = $request->file('file');
            $this->upload_comprobante($file, $entrada);
            // *** SUBIR COMPROBANTE

            $reporte = 'creo la entrada de códigos '.$entrada->folio.' de '.$entrada->editorial;
            $this->create_report($entrada->id, $reporte, 'proveedor', 'entradas');

            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($e->getMessage());
        }
        return response()->json(true);
    }

    public function upload_comprobante($file, $entrada){
        $extension = $file->getClientOriginalExtension();
        $name_file = time().".".$extension;
        $ruta = str_replace(' ', '-', env('APP_NAME')).'/entradas/comprobantes/';
        // // $public_url = null;

        Storage::disk('dropbox')->putFileAs($ruta, $file, $name_file);
        
        // // if($extension == 'pdf'){
            $public_url = $this->getSharedLink($ruta.$name_file)['url'];
        // // }

        // // TEMPORAL
        // $public_url = $ruta.$entrada->id;
        // $name_file = $ruta.$entrada->id;
        // $extension = 'pendiente';
        // // TEMPORAL

        Comprobante::create([
            'entrada_id' => $entrada->id,
            'name' => $name_file,
            'extension' => $extension,
            'public_url' => $public_url
            // 'size' => $response['size'],
        ]);
    }

    public function getSharedLink($file){
        $client = new ClienteDropbox(env('DROPBOX_TOKEN'));
        return $client->createSharedLinkWithSettings(
            $file, ["requested_visibility" => "public"]
        );
    }

    // OBTENER URL DE LA FOTO
    public function comprobante_url(Request $request){
        $comprobante = Comprobante::find($request->comprobante_id);
        $public_url = $comprobante->public_url;
        
        if($public_url == NULL){
            $ruta = str_replace(' ', '-', env('APP_NAME')).'/entradas/comprobantes/'.$comprobante->name;
            $url = Storage::disk('dropbox')->url($ruta);
        } else {
            $url = str_replace('www.dropbox.com', 'dl.dropboxusercontent.com', $public_url);
        }

        // if($comprobante->extension == 'pdf'){
        //     $url = $comprobante->public_url;
        //     // $url = str_replace('www.dropbox.com', 'dl.dropboxusercontent.com', $public_url);
        // } else { }
        
        return response()->json($url);
    }

    public function save_registros($request_items, $entrada){
        $unidades = 0;
        $entradas = collect($request_items);
        $hoy = Carbon::now();
        $entradas->map(function($item) use($entrada, &$unidades, $hoy){
            $unidades_base = (int) $item['unidades'];
            $libro_id = $item['id'];
            // CREAR LISTA DE REGISTROS
            $registro = Registro::create([
                'entrada_id' => $entrada->id,
                'libro_id'  => $libro_id,
                'unidades'  => $unidades_base,
                'unidades_que'  => $item['unidades_que'],
                'unidades_pendientes'  => $unidades_base,
                'created_at' => $hoy,
                'updated_at' => $hoy
            ]);

            $reporte = 'registro la entrada (entrada) de '.$registro->unidades.' unidades - '.$registro->libro->editorial.': '.$registro->libro->type.' '.$registro->libro->ISBN.' / '.$registro->libro->titulo.' para '.$entrada->folio.' / '.$entrada->editorial;
            $this->create_report($registro->id, $reporte, 'libro', 'registros');

            // AUMENTAR PIEZAS DE LOS LIBROS AGREGADOS
            \DB::table('libros')->whereId($libro_id)
                ->increment('piezas', $unidades_base);   
            
            $unidades += $unidades_base;
        });

        return $unidades;
    }
    
    // ACTUALIZAR DATOS DE ENTRADA
    // Función utilizada en EntradasComponent
    public function update(Request $request){
        $entrada = Entrada::whereId($request->id)->first();
        \DB::beginTransaction();
        try {
            $this->save_registros($request->nuevos, $entrada);

            $entrada->folio = strtoupper($request->folio);
            $entrada->editorial = $request->editorial;
            $entrada->unidades = $request->unidades;
            $entrada->save();

            \DB::commit();

        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json($entrada);
    }

    // MOSTRAR ENTRADAS POR EDITORIAL
    // Función utilizada en EntradasComponent, EditarEntradasComponent, VendidosComponent
    public function by_editorial(Request $request){
        $editorial = $request->editorial;
        $entradas = Entrada::where('editorial','like','%'.$editorial.'%')
                            ->orderBy('id','desc')->paginate(20);
        return response()->json($entradas);
    }

    // MOSTRAR ENTRADAS POR FECHA
    // Función utilizada en EditarEntradasComponent
    public function by_fecha(Request $request){
        $editorial 	= $request->editorial;
        $imprenta_id = $request->imprenta_id;
        $fechas = $this->format_date($request->inicio, $request->final);
        $fecha1 = $fechas['inicio'];
        $fecha2 = $fechas['final'];

        $query = Entrada::whereBetween('created_at', [$fecha1, $fecha2])->orderBy('id','desc');

        if($imprenta_id == null && ($editorial === null || $editorial == 'TODAS')){
            $entradas = $query->paginate(20);
        } else {
            if($imprenta_id == null){
                $entradas = $query->where('editorial', $editorial)->paginate(20);
            } else {
                $entradas = $query->where('imprenta_id', $imprenta_id)->paginate(20);
            }
        }
        return response()->json($entradas);
    }

    // MOSTRAR DETALLES DE UNA ENTRADA
    // Función utilizada en EditarEntradasComponent, EntradasComponent
    public function detalles_entrada(Request $request){
        $entrada_id = $request->entrada_id;
        $entrada = Entrada::whereId($entrada_id)->with(['repayments', 'registros.libro', 'registros.codes', 'registros.pack', 'imprenta', 'comprobantes'])->first(); 
        $entdevoluciones = Entdevolucione::where('entrada_id', $entrada_id)->with('registro.libro')->get();
        return response()->json(['entrada' => $entrada, 'entdevoluciones' => $entdevoluciones]);
    }

    // GUARDAR COSTOS DE LA ENTRADA
    // Función utilizada en EditarEntradasComponent
    public function update_costos(Request $request){
        \DB::beginTransaction();
        try {
            $total = 0;
            $lista_items = collect($request->items);
            $lista_items->map(function($item) use(&$total){
                Registro::whereId($item['id'])->update([
                    'costo_unitario' => (float) $item['costo_unitario'],
                    'total' => (double) $item['total']
                ]);
                $total += $item['total'];
            });

            $entrada = Entrada::find($request->id);
            $entrada->total = $total;
            $entrada->save();

            $ectotale = $this->get_we_ectotale($entrada->editorial, $entrada->corte_id);
            $ectotale->update([
                'total' => $ectotale->total + $entrada->total,
                'total_pagar' => $ectotale->total_pagar + $entrada->total
            ]);
            
            $editorial = Enteditoriale::where('editorial', $entrada->editorial)->first();
            if($editorial === null){
                $this->create_enteditoriale($entrada->editorial, $entrada->total);

            } else {
                $editorial->update([
                    'total' => $editorial->total + $entrada->total,
                    'total_pendiente' => $editorial->total_pendiente + $entrada->total
                ]);

            }

            $reporte = 'registro los costos de la entrada '.$entrada->folio.' de '.$entrada->editorial;
            $this->create_report($entrada->id, $reporte, 'proveedor', 'entradas');

            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json($entrada);
    }

    // Obtener cctotale
    public function get_ectotale($corte_id, $editoriale_id){
        return Ectotale::where([
            'corte_id' => $corte_id,
            'editoriale_id' => $editoriale_id
        ])->first();
    }

    public function create_enteditoriale($editorial, $t){
        Enteditoriale::create([
            'editorial' => $editorial,
            'total' => $t,
            'total_pendiente' => $t
        ]);
    }

    // GUARDAR PAGO DE ENTRADA
    // Función utilizada en EditarEntradasComponent
    public function pago_entrada(Request $request){
        try {
            \DB::beginTransaction();
            $entrada = Entrada::whereId($request->entrada_id)->first();
            $repayment = Repayment::create([
                'entrada_id'    => $entrada->id,
                'pago'          => $request->pago
            ]);
            $entrada->update([
                'total_pagos' => $entrada->total_pagos + $request->pago
            ]);
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        
        return response()->json($entrada);
    }

    // BUSCAR ENTRADA POR FOLIO
    // Función utilizada en EntradasComponent
    public function buscarFolio(Request $request){
        $folio = $request->folio;
        $entrada = Entrada::where('folio', $folio)->first();
        return response()->json($entrada);
    }

    // IMPRIMIR REPORTE DE ENTRADA
    public function downloadEntrada($id){
        $entrada = Entrada::find($id);
        $name_archivo = 'entrada_' . $entrada->folio . '.xlsx';
        return Excel::download(new EntradaExport($entrada->id), $name_archivo);
    }

    // DESCARGAR TODAS LAS ENTRADAS
    public function downEntradas($inicio, $final, $editorial){
        $data['fecha'] = Carbon::now();
        $data['inicio'] = $inicio;
        $data['final'] = $final;

        if($final != '0000-00-00'){
            $fechas = $this->format_date($inicio, $final);
            $inicio = $fechas['inicio'];
            $final = $fechas['final'];
        }

        if($final === '0000-00-00' && $editorial === 'TODAS')
            $entradas = Entrada::orderBy('id','desc')->get(); 
        if($final !== '0000-00-00' && $editorial === 'TODAS')
            $entradas = Entrada::whereBetween('created_at', [$inicio, $final])->orderBy('id','desc')->get();
        if($final === '0000-00-00' && $editorial !== 'TODAS')
            $entradas = Entrada::where('editorial', $editorial)->orderBy('id','desc')->get();
        if($final !== '0000-00-00' && $editorial !== 'TODAS'){
            $entradas = Entrada::where('editorial', $editorial)
                        ->whereBetween('created_at', [$inicio, $final])
                        ->orderBy('id','desc')->get();
        }
        $totales = $this->acumular_totales($entradas);
        $data['total_unidades'] = $totales['total_unidades'];
        $data['total'] = $totales['total'];
        $data['total_pagos'] = $totales['total_pagos'];
        $data['total_pendiente'] = $totales['total_pendiente'];
        $data['total_devolucion'] = $totales['total_devolucion'];
        $data['editorial'] = $editorial;
        $data['entradas'] = $entradas;
        
        $pdf = PDF::loadView('download.pdf.entradas.reporte-gral', $data);
        return $pdf->download('reporte-entradas.pdf');
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

    public function acumular_totales($entradas){
        $total_unidades = 0;
        $total = 0;
        $total_pagos = 0;
        $total_pendiente = 0;
        $total_devolucion = 0;
        foreach($entradas as $entrada){
            $total_unidades += $entrada->unidades;     
            $total += $entrada->total;
            $total_pagos += $entrada->total_pagos;
            $total_devolucion += $entrada->total_devolucion;
            $total_pendiente += $entrada->total - $entrada->total_pagos;

        }
        $totales = [
            'total_unidades' => $total_unidades,
            'total' => $total,
            'total_pagos' => $total_pagos,
            'total_pendiente' => $total_pendiente,
            'total_devolucion' => $total_devolucion
        ];
        return $totales;
    }

    // DESCARGAR REPORTE DE ENTRADAS EN EXCEL
    public function downEntradasEXC($inicio, $final, $editorial, $tipo){
        return Excel::download(new EntradasExport($inicio, $final, $editorial, $tipo), 'reporte-entradas.xlsx');
    }

    // ELIMINAR REGISTRO DE ENTRADA (ELIMINADO DEL COMPONENTE)
    // Función utilizada en EntradasComponent
    public function eliminar(Request $request){
        // try {
        //     \DB::beginTransaction();

        //     $registro = Registro::whereId($request->id)->update(['estado' => 'Eliminado']);

        //     \DB::commit();
            
        //     return response()->json($registro);
        
        // } catch (Exception $e) {
        //     \DB::rollBack();
        //     return response()->json($exception->getMessage());
		// }
    }
    
    // VERIFICAR QUE EL REGISTRO ESTE EN ESTADO ELIMINADO (FUNCIÓN ELIMINADA DEL CONTROLADOR)
    public function concluir_registro($id){
        
    }

    // GUARDAR DEVOLUCIÓN *CHECK
    public function devolucion(Request $request){
        $entrada = Entrada::whereId($request->id)->first();
        \DB::beginTransaction();
        try {
            $total = 0;
            $items = collect($request->registros);
            $hoy = Carbon::now();
            // $codes_ids = collect(); &$codes_ids, 
            $items->map(function($item) use($entrada, &$total, $hoy){
                $unidades_base = (int) $item['unidades_base'];
                $total_base = (double) $item['total_base'];
                $registro_id = $item['id'];
                if($unidades_base > 0){
                    $entdevolucione = Entdevolucione::create([
                        'entrada_id' => $entrada->id,
                        'registro_id' => $registro_id,
                        'unidades' => $unidades_base,
                        'total' => $total_base,
                        'creado_por' => auth()->user()->name,
                        'created_at' => $hoy,
                        'updated_at' => $hoy
                    ]);

                    // DISMINUIR UNIDADES PENDIENTE
                    $registro = Registro::whereId($registro_id)->first();
                    $registro->update([
                        'unidades_pendientes' => $registro->unidades_pendientes - $unidades_base
                    ]);

                    $reporte = 'registro la devolución (entrada) de '.$entdevolucione->unidades.' unidades - '.$registro->libro->editorial.': '.$registro->libro->type.' '.$registro->libro->ISBN.' / '.$registro->libro->titulo.' para '.$entrada->folio.' / '.$entrada->editorial;
                    $this->create_report($entdevolucione->id, $reporte, 'libro', 'entdevoluciones');

                    // \DB::table('registros')->whereId($registro_id)
                    //     ->decrement('unidades_pendientes',  $unidades_base);

                    // DISMINUIR PIEZAS DE LOS LIBROS
                    \DB::table('libros')->whereId($item['libro']['id'])
                        ->decrement('piezas', $unidades_base);

                    if($item['pack_id'] != null && $item['libro']['type'] == 'digital'){
                        $pack = Pack::whereId($item['pack_id'])->first();
                        $pack->update(['piezas' => $pack->piezas - $unidades_base ]);
                    }

                    // DEVOLUCION DE CODIGOS
                    $codes = $registro->codes()->whereIn('code_id', $item['code_registro'])->get();
                    $codes->map(function($code){
                        $code->update(['estado' => 'eliminado']);
                        $code->registros()
                            ->updateExistingPivot($code->pivot->registro_id, [
                                'devolucion' => true
                            ]);
                        //  use (&$codes_ids) $codes_ids->push($code->id);
                    });
                }
                $total += $total_base;
            });

            $entrada->update([
                'total_devolucion' => $entrada->total_devolucion + $total
            ]);

            $ectotale = $this->get_we_ectotale($entrada->editorial, $entrada->corte_id);
            $ectotale->update([
                'total_devolucion' => $ectotale->total_devolucion + $total,
                'total_pagar' => $ectotale->total_pagar - $total
            ]);

            $editorial = Enteditoriale::where('editorial', $entrada->editorial)->first();
            $editorial->update([
                'total_devolucion' => $editorial->total_devolucion + $total,
                'total_pendiente' => $editorial->total_pendiente - $total
            ]);

            // // SOLO SI EL PROVEEDOR ES MAJESTIC EDUCATION
            // if($entrada->editorial == 'MAJESTIC EDUCATION'){
            //     if($entrada->tipo == 'remision'){
            //         $remision = Remisione::on('opuesto')->whereId($entrada->folio)->first();
                
            //         $entregado_por = auth()->user()->name;
            //         $total_devolucion = 0;

            //         // DEVOLUCIONES
            //         $entdevoluciones = $entrada->entdevoluciones;
            //         $hoy = Carbon::now();
                    
            //         $entdevoluciones->map(function($entdevolucion) use($remision, $entregado_por, &$total_devolucion, $hoy){
            //             $unidades_base = $entdevolucion->unidades;
            //             $total_base = $entdevolucion->total;
                        
            //             if($unidades_base != 0){
            //                 // BUSCAR EL LIBRO EN MAJESTIC EDUCATION
            //                 $libro = Libro::on('opuesto')->where('titulo', $entdevolucion->registro->libro->titulo)->first();
            //                 // Buscar devolución
            //                 $devolucion = Devolucione::on('opuesto')->where([
            //                     'remisione_id' => $remision->id,
            //                     'libro_id' => $libro->id
            //                 ])->first();
                            
            //                 // Crear registros de fecha de la devolución
            //                 $fecha = Fecha::on('opuesto')->create([
            //                     'remisione_id' => $remision->id,
            //                     'fecha_devolucion' => $hoy->format('Y-m-d'),
            //                     'libro_id' => $libro->id,
            //                     'unidades' => $unidades_base,
            //                     'total' => $total_base,
            //                     'entregado_por' => $entregado_por,
            //                     'creado_por' => auth()->user()->name,
            //                     'created_at' => $hoy,
            //                     'updated_at' => $hoy
            //                 ]);
                            
            //                 $unidades = $devolucion->unidades + $unidades_base;
            //                 $total = $devolucion->total + $total_base;
            //                 $unidades_resta = $devolucion->unidades_resta - $unidades_base;
            //                 $total_resta = $devolucion->total_resta - $total_base;
                            
            //                 // Actualizar la tabla de devolución
            //                 $devolucion->update([
            //                     'unidades' => $unidades, 
            //                     'unidades_resta' => $unidades_resta,
            //                     'total' => $total,
            //                     'total_resta' => $total_resta
            //                 ]);

            //                 // AUMENTAR PIEZAS DE LOS LIBROS DEVUELTOS
            //                 $libro->update(['piezas' => $libro->piezas + $unidades_base]);  
            //             } 

            //             $total_devolucion += $total_base;
            //         });

            //         // DEVOLUCION DE CODIGOS
            //         $codes = Code::whereIn('id', $codes_ids)->get();
            //         $codes->map(function($code) use($remision){
            //             $c = Code::on('opuesto')->where('codigo', $code->codigo)->first();
            //             $c->update(['estado' => 'inventario']);;
            //             \DB::connection('opuesto')->table('code_dato')
            //                 ->whereIn('dato_id', $remision->datos->pluck('id'))
            //                 ->where('code_id', $c->id)->update([
            //                     'devolucion' => true
            //                 ]);
            //         });

            //         $total_pagar = $remision->total_pagar - $total_devolucion;
            //         $t_devolucion = $remision->total_devolucion + $total_devolucion;
                
            //         if ((int) $total_pagar === 0) {
            //             $remision->update(['estado' => 'Terminado']); 
            //         }

            //         // ACTUALIZAR REMISION
            //         $remision->update([
            //             'total_devolucion' => $t_devolucion,
            //             'total_pagar'   => $total_pagar
            //         ]);

            //         // ACTUALIZA LA CUENTA DEL CORTE CORRESPONDIENTE
            //         $cctotale = Cctotale::on('opuesto')->where([
            //             'cliente_id' => $remision->cliente_id,
            //             'corte_id'  => $remision->corte_id
            //         ])->first();
            //         $cctotale->update([
            //             'total_devolucion' => $cctotale->total_devolucion + $total_devolucion,
            //             'total_pagar' => $cctotale->total_pagar - $total_devolucion
            //         ]);

            //         // ACTUALIZAR CUENTA GENERAL DEL CLIENTE
            //         $remcliente = Remcliente::on('opuesto')->where('cliente_id', $remision->cliente_id)->first();
            //         $remcliente->update([
            //             'total_devolucion' => $remcliente->total_devolucion + $total_devolucion,
            //             'total_pagar' => $remcliente->total_pagar - $total_devolucion
            //         ]);
            //     }
            //     if($entrada->tipo == 'promocion'){
            //         $promotion = Promotion::on('opuesto')->where('folio', $request->folio)->first();
                    
            //         // DEVOLUCIONES
            //         $entdevoluciones = $entrada->entdevoluciones;
            //         $total_unidades = 0;
            //         $entdevoluciones->map(function($entdevolucion) use($promotion, &$total_unidades){
            //             $unidades_devolucion = $entdevolucion->unidades;
            //             if($unidades_devolucion != 0){
            //                 // BUSCAR EL LIBRO EN MAJESTIC EDUCATION
            //                 $libro = Libro::on('opuesto')->where('titulo', $entdevolucion->registro->libro->titulo)->first();
            //                 $libro_id = $libro->id;
            //                 $departure = Departure::on('opuesto')->where([
            //                     'promotion_id' => $promotion->id,
            //                     'libro_id' => $libro_id
            //                 ])->first();
                            
            //                 $pd = Prodevolucione::create([
            //                     'promotion_id' => $promotion->id, 
            //                     'libro_id' => $libro_id, 
            //                     'unidades' => $unidades_devolucion,
            //                     'creado_por' => auth()->user()->name
            //                 ]);
            
            //                 // ACTUALIZAR UNIDADES PENDIENTES DE ESE LIBRO EN ESA PROMOCION
            //                 $departure->update([
            //                     'unidades_pendientes' => $departure->unidades_pendientes - $unidades_devolucion
            //                 ]);
            
            //                 if($libro->type != 'digital'){
            //                     // AUMENTAR PIEZAS DE LOS LIBROS AGREGADOS
            //                     $libro->update(['piezas' => $libro->piezas + $unidades_devolucion]);
            //                 }
            //                 $total_unidades += $unidades_devolucion;
            //             }
            //         });
            
            //         // DEVOLUCION DE CODIGOS
            //         $codes = Code::whereIn('id', $codes_ids)->get();
            //         $codes->map(function($code) use($promotion){
            //             $c = Code::on('opuesto')->where('codigo', $code->codigo)->first();
            //             $c->update(['estado' => 'inventario']);;
            //             \DB::connection('opuesto')->table('code_departure')
            //                 ->whereIn('departure_id', $promotion->departures->pluck('id'))
            //                 ->where('code_id', $c->id)->update([
            //                     'devolucion' => true
            //                 ]);
            //         });
            
            //         $unidades_devolucion = $promotion->unidades_devolucion + $total_unidades;
            //         $unidades_pendientes = $promotion->unidades - $unidades_devolucion;
            //         $promotion->update([
            //             'unidades_devolucion' => $unidades_devolucion,
            //             'unidades_pendientes' => $unidades_pendientes
            //         ]);
            //     }
            // }

            $reporte = 'registro la devolución de la entrada '.$entrada->folio.' de '.$entrada->editorial;
            $this->create_report($entrada->id, $reporte, 'proveedor', 'entdevoluciones');

            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json($entrada);
    }

    public function get_we_ectotale($editorial, $corte_id){
        $e = \DB::table('editoriales')->where('editorial', $editorial)->first();
        return Ectotale::where([
                    'corte_id' => $corte_id,
                    'editoriale_id' => $e->id
                ])->first();
    }

    public function pagos_entrada(){
        // $entradas = \DB::table('entradas')
        //             ->select(
        //                 'editorial',
        //                 // \DB::raw('SUM(unidades) as unidades'),
        //                 \DB::raw('SUM(total) as total'),
        //                 \DB::raw('SUM(total_pagos) as total_pagos'),
        //                 \DB::raw('SUM(total_devolucion) as total_devolucion')
        //             )->groupBy('editorial')->orderBy('editorial', 'asc')->get();
        $editoriales = Enteditoriale::orderBy('editorial', 'asc')->withCount('entdepositos')->get();
        return response()->json($editoriales);
    }

    // GUARDAR PAGO
    public function save_pago(Request $request){
        $editorial = Enteditoriale::whereId($request->enteditoriale_id)->first();
        $e = Editoriale::where('editorial', $editorial->editorial)->first();
        \DB::beginTransaction();
        try {
            $monto = (double) $request->pago;
            $corte_id = $request->corte_id;
            $editoriale_id = $e->id;
            $corte_id_favor = $request->corte_id_favor;

            // *** SUBIR COMPROBANTE
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            $name_file = time().".".$extension;

            $ruta = str_replace(' ', '-', env('APP_NAME')).'/proveedores/pagos/';
            Storage::disk('dropbox')->putFileAs($ruta, $request->file('file'), $name_file);
            $public_url = $this->getSharedLink($ruta.$name_file)['url'];
            // *** SUBIR COMPROBANTE

            $deposito = Entdeposito::create([
                'enteditoriale_id' => $editorial->id,
                'corte_id' => $corte_id,
                'pago' => $monto,
                'fecha' => $request->fecha,
                'nota' => $request->nota,
                'ingresado_por' => auth()->user()->name,
                'name' => $name_file,
                'extension' => $extension,
                'public_url' => $public_url,
                // 'size' => $response['size'],
            ]);
            
            $this->validate_favor($corte_id, $editoriale_id, $corte_id_favor, $monto);
            
            $editorial->update([
                'total_pagos' => $editorial->total_pagos + $monto,
                'total_pendiente' => $editorial->total_pendiente - $monto
            ]);

            $reporte = 'registro un pago al proveedor '.$editorial->editorial.' PAGO: '.$deposito->fecha.' / $'.$deposito->pago.' / '.$deposito->nota;
            $this->create_report($deposito->id, $reporte, 'proveedor', 'entdepositos');

            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json(true);
    }

    public function validate_favor($corte_id, $editoriale_id, $corte_id_favor, $monto){
        $ectotale = $this->get_ectotale($corte_id, $editoriale_id);
        if($corte_id_favor == 'null') {
            $this->update_ectotale($ectotale, $monto);
        } else {
            $total_favor = $monto - $ectotale->total_pagar;
            $ectotale->update([
                'corte_id_favor' => $corte_id_favor,
                'total_favor' => $ectotale->total_favor + $total_favor,
                'total_pagos' => $ectotale->total_pagos + $ectotale->total_pagar,
                'total_pagar' => 0
            ]);

            $ectotale_favor = $this->get_ectotale($corte_id_favor, $editoriale_id);
            $this->update_ectotale($ectotale_favor, $total_favor);
        }
    }

    public function update_ectotale($ectotale, $monto){
        $ectotale->update([
            'total_pagos' => $ectotale->total_pagos + $monto,
            'total_pagar' => $ectotale->total_pagar - $monto
        ]);
    }

    // ACTUALIZAR PAGO
    public function update_pago(Request $request){
        $entdeposito = Entdeposito::find($request->id);

        $pago_anterior = $entdeposito->fecha.' / $'.$entdeposito->pago.' / '.$entdeposito->nota;
        $pago = (double) $request->pago;
        $enteditorial = Enteditoriale::find($entdeposito->enteditoriale_id);

        \DB::beginTransaction();
        try {
            $total_pagos = ($enteditorial->total_pagos - $entdeposito->pago) + $pago;
            $total_pendiente = ($enteditorial->total_pendiente + $entdeposito->pago) - $pago;
            $enteditorial->update([
                'total_pagos' => $total_pagos,
                'total_pendiente' => $total_pendiente
            ]);
            $entdeposito->update([
                'pago' => $pago,
                'fecha' => $request->fecha,
                'nota' => $request->nota,
            ]);

            $pago_nuevo = $entdeposito->fecha.' / $'.$entdeposito->pago.' / '.$entdeposito->nota;
            $reporte = 'edito el pago al proveedor '.$enteditorial->editorial.': '.$pago_anterior.' a '.$pago_nuevo;
            $this->create_report($entdeposito->id, $reporte, 'proveedor', 'entdepositos');

            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }

        return response()->json($entdeposito);
    }

    // ELIMINAR DEPOSITO
    public function delete_pago(Request $request){
        $entdeposito = Entdeposito::find($request->pago_id);
        $enteditorial = Enteditoriale::find($entdeposito->enteditoriale_id);

        \DB::beginTransaction();
        try {
            $enteditorial->update([
                'total_pagos' => $enteditorial->total_pagos - $entdeposito->pago,
                'total_pendiente' => $enteditorial->total_pendiente + $entdeposito->pago
            ]);

            $reporte = 'elimino un pago al proveedor '.$enteditorial->editorial.' PAGO: '.$entdeposito->fecha.' / $'.$entdeposito->pago.' / '.$entdeposito->nota;
            $this->create_report($entdeposito->id, $reporte, 'proveedor', 'entdepositos');

            $entdeposito->delete();
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json(true);
    }

    public function enteditoriale_pagos(Request $request){
        $id = $request->id;
        $enteditoriale = Enteditoriale::find($id);
        $entdepositos = Entdeposito::where('enteditoriale_id', $enteditoriale->id)
                                ->orderBy('created_at', 'desc')->get();
        $ids_entradas = Entrada::where('editorial', $enteditoriale->editorial)
                            ->select('id')
                            ->get();
        
        $ids = [];
        $ids_entradas->map(function($ie) use (&$ids){
            $ids[] = $ie->id;
        });

        $entdevoluciones = \DB::table('entdevoluciones')
                            ->join('entradas', 'entdevoluciones.entrada_id', '=', 'entradas.id')
                            ->join('registros', 'entdevoluciones.registro_id', '=', 'registros.id')
                            ->join('libros', 'registros.libro_id', '=', 'libros.id')
                            ->whereIn('entdevoluciones.entrada_id', $ids)
                            ->select('entdevoluciones.created_at', 'entradas.folio', 'entdevoluciones.creado_por', 'libros.ISBN', 'libros.titulo', 'registros.costo_unitario', 'entdevoluciones.unidades', 'entdevoluciones.total')
                            ->orderBy('entdevoluciones.created_at', 'desc')
                            ->get();

        // Entdevolucione::whereIn('entrada_id', $ids)
        //                     ->with('entrada')->get();
        return response()->json(['entdepositos' => $entdepositos, 'entdevoluciones' => $entdevoluciones]);
    }

    public function descargar_gralEdit(){
        return Excel::download(new EAccountExport, 'lista-editoriales.xlsx');
    }

    // ENVIAR UNIDADES A ME
    public function send_me(Request $request){
        $entrada_id = $request->entrada_id;
        $entrada = Entrada::find($entrada_id);
        $registros = Registro::where('entrada_id', $entrada_id)->get();
        
        $folio = strtoupper($entrada->folio);
        \DB::beginTransaction();
        try {
            $fecha = Carbon::now();
            $this->create_me_entrada($folio, $entrada->editorial, $registros->sum('unidades_que'), $fecha);

            $me_entrada = $this->get_me_entrada($folio);
            $reg_datos = [];
            $registros->map(function($registro) use(&$reg_datos, $me_entrada, $fecha){
                $unidades_que = $registro->unidades_que;
                $me_libro = $this->get_me_libro($registro->libro->titulo);
                $reg_datos[] = $this->set_me_datos($me_entrada->id, $me_libro->id, $unidades_que, $fecha);
                $this->set_me_libro_increment($me_libro->id, $unidades_que);

                $reporte = 'registro la salida (entrada) de '.$unidades_que.' unidades - '.$registro->libro->editorial.': '.$registro->libro->type.' '.$registro->libro->ISBN.' / '.$registro->libro->titulo.' para '.$registro->entrada->folio.': QUERÉTARO / MAJESTIC EDUCATION';
                $this->create_report($registro->id, $reporte, 'libro', 'registros');
                
            });

            $this->set_me_registros($reg_datos);
            
            $entrada->update(['lugar' => 'QUE']);

            $reporte = 'envió libros de la entrada '.$entrada->folio.' a QUERÉTARO / MAJESTIC EDUCATION';
            $this->create_report($entrada->id, $reporte, 'proveedor', 'entradas');

            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json(true);
    }

    public function create_me_entrada($folio, $editorial, $unidades, $fecha){
        \DB::connection('majesticeducation')->table('entradas')
            ->insert([
                'folio' => $folio,
                'editorial' => $editorial,
                'unidades' => $unidades,
                'created_at' => $fecha,
                'updated_at' => $fecha
            ]);
    }

    public function get_me_entrada($folio){
        return \DB::connection('majesticeducation')->table('entradas')
                    ->where('folio', $folio)->first();
    }

    public function get_me_libro($titulo){
        return \DB::connection('majesticeducation')->table('libros')
            ->where('titulo', $titulo)->first();
    }

    public function set_me_datos($entrada_id, $libro_id, $unidades, $fecha){
        return [
            'entrada_id' => $entrada_id, 
            'libro_id' => $libro_id,
            'unidades' => $unidades,
            'created_at' => $fecha,
            'updated_at' => $fecha
        ];
    }

    public function set_me_libro_increment($libro_id, $unidades){
        \DB::connection('majesticeducation')->table('libros')
                    ->where('id', $libro_id)
                    ->increment('piezas', $unidades);
    }

    public function set_me_registros($reg_datos){
        \DB::connection('majesticeducation')->table('registros')
                                ->insert($reg_datos);
    }

    public function send_salida(Request $request){
        $salida_id = $request->salida_id;
        $salida = Salida::find($salida_id);

        $folio = $salida->folio;
        \DB::beginTransaction();
        try {
            $fecha = Carbon::now();
            $this->create_me_entrada($folio, 'MAJESTIC EDUCATION', $salida->unidades, $fecha);
            $me_entrada = $this->get_me_entrada($folio);

            $reg_datos = [];
            $salida->sregistros->map(function($registro) use(&$reg_datos, $me_entrada, $fecha){
                $unidades = $registro->unidades;
                $me_libro = $this->get_me_libro($registro->libro->titulo);
                $reg_datos[] = $this->set_me_datos($me_entrada->id, $me_libro->id, $unidades, $fecha);

                // INCREMENTAR PIEZAS EN ME
                $this->set_me_libro_increment($me_libro->id, $unidades);
                // DISMINUIR PIEZAS DE LOS LIBROS
                \DB::table('libros')->whereId($registro->libro_id)
                    ->decrement('piezas', $unidades);

                $reporte = 'registro la salida (salida) de '.$registro->unidades.' unidades - '.$registro->libro->editorial.': '.$registro->libro->type.' '.$registro->libro->ISBN.' / '.$registro->libro->titulo.' para '.$registro->salida->folio.': QUERÉTARO / MAJESTIC EDUCATION';
                $this->create_report($registro->id, $reporte, 'libro', 'sregistros');
            });
            $this->set_me_registros($reg_datos);
            $salida->update(['estado' => 'enviado']);

            $reporte = 'envió la salida '.$salida->folio.' a QUERÉTARO / MAJESTIC EDUCATION';
            $this->create_report($salida->id, $reporte, 'proveedor', 'salidas');

            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json(true);
    }

    public function create_report($entrada_id, $reporte, $type, $tabla){
        Reporte::create([
            'user_id' => auth()->user()->id, 
            'type' => $type,  
            'reporte' => $reporte,
            'name_table' => $tabla, 
            'id_table' => $entrada_id
        ]);
    }

    public function save_editorial(Request $request){
        \DB::beginTransaction();
        try {
            $e = Editoriale::create([
                'editorial' => strtoupper($request->editorial)
            ]);

            $this->create_enteditoriale($e->editorial, 0);
            
            $corte = $this->get_corte();

            Ectotale::create([
                'corte_id' => $corte->id, 
                'editoriale_id' => $e->id
            ]);

            $reporte = 'creo la editorial '.$e->editorial;
            $this->create_report($e->id, $reporte, 'proveedor', 'editoriales');
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json($request);
    }

    // GUARDAR IMPRENTA EN DB
    public function save_imprenta(Request $request){
        \DB::beginTransaction();
        try {
            $imprenta = Imprenta::create([
                'imprenta' => strtoupper($request->imprenta),
                'tipo' => strtoupper($request->tipo)
            ]);

            $reporte = 'creo la imprenta '.$imprenta->imprenta;
            $this->create_report($imprenta->id, $reporte, 'proveedor', 'imprentas');
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($e->getMessage());
        }
        return response()->json(true);
    }

    public function get_corte(){
        $hoy = Carbon::now();
        return Corte::where('inicio', '<', $hoy)
                        ->where('final', '>', $hoy)
                        ->first();
    }

    public function cortes($editorial){
        return view('information.cortes.proveedores.details-editorial', compact('editorial'));
    }

    public function get_cortes(Request $request){
        $e = $request->editorial;
        $editoriale = Editoriale::where('editorial', $e)->first();
        $enteditoriale = Enteditoriale::where('editorial', $e)->first();
        $ectotales = Ectotale::where('editoriale_id', $editoriale->id)->with('corte')->orderBy('corte_id', 'desc')->get();
        return response()->json([
            'editoriale' => $editoriale,
            'enteditoriale' => $enteditoriale,
            'ectotales' => $ectotales
        ]);
    }

    public function cortes_details(Request $request){
        $corte_id = $request->corte_id;
        $entdepositos = Entdeposito::where([
            'corte_id' => $corte_id,
            'enteditoriale_id' => $request->enteditoriale_id
        ])->orderBy('created_at', 'desc')->get();
        $entradas = Entrada::where([
            'corte_id' => $corte_id,
            'editorial' => $request->editorial
        ])->orderBy('id','desc')->get();
        $ids = $entradas->pluck('id');
        $entdevoluciones = Entdevolucione::whereIn('entrada_id', $ids->all())
                        ->with('registro.libro')
                        ->orderBy('created_at', 'desc')->get();
        return response()->json([
            'entdepositos' => $entdepositos,
            'entradas' => $entradas,
            'entdevoluciones' => $entdevoluciones
        ]);
    }

    public function get_imprentas($tipo){
        $query = \DB::table('imprentas')->orderBy('imprenta', 'asc');
        if($tipo == 'all') $imprentas = $query->get();
        else $imprentas = $query->where('tipo', $tipo)->get();
        
        return response()->json($imprentas);
    }

    // BUSCAR ENTRADAS POR IMPRENTA
    public function by_imprenta(Request $request){
        $entradas = Entrada::where('imprenta_id', $request->imprenta_id)
                            ->orderBy('id','desc')->paginate(20);
        return response()->json($entradas);
    }

    // AGREGAR/ACTUALIZAR ENTRADA
    public function addupdate($entrada_id, $agregar){
        $entrada = Entrada::whereId($entrada_id)->first();
        return view('information.entradas.addupdate', compact('agregar'));
    }
}
