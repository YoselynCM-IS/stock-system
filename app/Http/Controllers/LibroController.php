<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Enteditoriale;
use App\Libro;
use App\Entrada;
use App\Registro;
use App\Devolucione;
use Carbon\Carbon;
use App\Exports\LibrosExport;
use App\Exports\MovLibrosExport;
use App\Exports\MovFechasExport;
use App\Exports\MovMontoExport;
use App\Exports\libros\BothExport;
use App\Exports\libros\ClavesExport;
use App\Exports\libros\ScratchExport;
use App\Exports\EntSal\EntSalExport;
use App\Exports\movimientos\MovDayLibrosExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Remisione;
use App\Fecha;
use App\Note;
use App\Promotion;
use App\Regalo;
use App\Entdevolucione;
use App\Reporte;
Use App\Defectuoso;
use App\Mail\movimientos\LibrosDay;
use Illuminate\Support\Facades\Mail;
use App\Serie;
use App\Pack;
use App\Code;

class LibroController extends Controller
{

    // MOSTRAR VISTA PRINCIPAL DE LIBROS
    public function lista(){
        $types = \DB::table('libros')->select('type')->distinct()->get()->pluck('type');
        $editoriales = \DB::table('editoriales')->orderBy('editorial', 'asc')->get();
        return view('information.libros.lista', compact('editoriales', 'types'));
    } 

    // MOSTRAR VISTA PRINCIPAL DE LAS ENTRADAS-SALIDAS
    public function lista_entradas_salidas(){
        return view('information.movimientos.entradas-salidas');
    }

    // MOSTRAR TODOS LOS LIBROS
    public function index(){
        $libros = $this->all_libros_paginate()->paginate(25);
        $resultados = $this->get_all_detallado($libros);
        return response()->json(['libros' => $resultados, 'paginate' => $libros]);
    }

    public function all_libros_paginate(){
        return \DB::table('libros')->join('series', 'series.id', '=', 'libros.serie_id')
                    ->select('libros.*', 'series.id as serie_id', 'series.serie')
                    ->orderBy('libros.editorial', 'asc')
                    ->orderBy('series.serie', 'asc')
                    ->orderBy('libros.type', 'desc')
                    ->orderBy('libros.titulo', 'asc')
                    ->where('libros.estado', 'activo');
    }

    public function get_all_detallado($libros){
        $resultados = collect();
        $libros->map(function($libro) use(&$resultados){
            $resultados->push($this->assign_datos_libro($libro, $libro->serie));
        });
        return $resultados;
    }

    // ASIGNAR VALORES DE LIBRO
    public function assign_datos_libro($libro, $serie){
        return [
            'id' => $libro->id, 
            'serie_id' => $libro->serie_id,
            'serie' => $serie,
            'ISBN' => $libro->ISBN,  
            'titulo' => $libro->titulo, 
            'autor' => $libro->autor, 
            'editorial' => $libro->editorial, 
            'edicion' => $libro->edicion,
            'piezas' => $libro->piezas,
            'defectuosos' => $libro->defectuosos,
            'estado' => $libro->estado,
            'type' => $libro->type,
            'externo' => $libro->externo
        ];
    }

    // OBTENER SOLOS Y SCRATCH
    public function get_only_scratch($sistema, $id, $piezas, $type){
        $sum_scratch = 0;
        $count_solo = $piezas;
        if($type != 'promocion'){
            $sum_scratch = Pack::on($sistema)->where('libro_fisico', $id)
                    ->OrWhere('libro_digital', $id)
                    ->sum('piezas');

            if($type == 'digital') {
                $count_solo = Code::on($sistema)->where('libro_id', $id)
                                ->where('tipo', 'alumno')
                                ->where('estado', 'inventario')->count();
            }
            if($type == 'venta')
                $count_solo = $piezas - $sum_scratch;
        }
        return [
            'sum_scratch' => $sum_scratch, 
            'count_solo' => $count_solo,  
        ];
    }

    // MOSTRAR COINCIDENCIAS DE TITULO PAGINADO
    public function by_titulo(Request $request){
        $libros = $this->all_libros_paginate()
                    ->where('titulo','like','%'.$request->titulo.'%')
                    ->paginate(25);
        $resultados = $this->get_all_detallado($libros);
        return response()->json(['libros' => $resultados, 'paginate' => $libros]);
    }

    // MOSTRAR COINCIDENCIAS DE ISBN PAGINADO
    public function by_isbn(Request $request){
        $libros = $this->all_libros_paginate()
                    ->where('ISBN','like','%'.$request->isbn.'%')
                    ->paginate(25);
        $resultados = $this->get_all_detallado($libros);
        return response()->json(['libros' => $resultados, 'paginate' => $libros]);
    }

    // BUSCAR LIBRO POR EDITORIAL paginado
    // Función utilizada en LibrosComponent
    public function by_editorial(Request $request){
        $libros = $this->all_libros_paginate()
                    ->where('editorial', $request->editorial)
                    ->paginate(25);
        $resultados = $this->get_all_detallado($libros);
        return response()->json(['libros' => $resultados, 'paginate' => $libros]);
    }

    // OBTENER LIBROS POR SERIE
    public function by_serie(Request $request){
        $libros = $this->all_libros_paginate()
                    ->where('serie_id', $request->serie_id)
                    ->paginate(25);
        $resultados = $this->get_all_detallado($libros);
        return response()->json(['libros' => $resultados, 'paginate' => $libros]);
    }

    // OBTENER LIBROS POR TIPO
    public function by_type(Request $request){
        $libros = $this->all_libros_paginate()
                    ->where('type', $request->type)
                    ->paginate(25);
        $resultados = $this->get_all_detallado($libros);
        return response()->json(['libros' => $resultados, 'paginate' => $libros]);
    }

    // OBTENER LIBROS POR TODOS LOS FILTROS
    public function by_all(Request $request){
        $libros = $this->all_libros_paginate()
                ->when($request->filled('editorial'), function ($query) use($request) {
                    $query->where('editorial', $request->editorial);
                })->when($request->filled('serie_id'), function ($query) use($request) {
                    $query->where('serie_id', $request->serie_id);
                })->when($request->filled('titulo'), function ($query) use($request) {
                    $query->where('titulo','like','%'.$request->titulo.'%');
                })->when($request->filled('isbn'), function ($query) use($request) {
                    $query->where('ISBN','like','%'.$request->isbn.'%');
                })->when($request->filled('type'), function ($query) use($request) {
                    $query->where('type', $request->type);
                })->paginate(25);
        $resultados = $this->get_all_detallado($libros);
        return response()->json(['libros' => $resultados, 'paginate' => $libros]);
    }

    // BUSCAR LIBRO POR ISBN
    // Función utilizada en
    // - AdeudosComponent - DevoluciónAdeudosComponent - EntradasComponent
    // - NewNotaComponent - PromocionesComponent - RemisionComponent
    public function show(Request $request){
        $isbn = $request->isbn;
        $libros = \DB::table('libros')
                    ->where('ISBN','like','%'.$isbn.'%')
                    ->where('estado', 'activo')
                    ->orderBy('titulo', 'asc')->get();
        return response()->json($libros);
    }

    // MOSTRAR LIBROS POR ISBN Y TITULO
    public function by_isbn_editorial(Request $request){
        $libros = \DB::table('libros')
                    ->select('id', 'ISBN', 'titulo', 'editorial', 'piezas', 'defectuosos')
                    ->where('editorial', $request->editorial)
                    ->where('ISBN','like','%'.$request->isbn.'%')
                    ->where('estado', 'activo')
                    ->orderBy('titulo', 'asc')->get();
        return response()->json($libros);
    }

    public function isbn_por_editorial(Request $request){
        $isbn = $request->isbn;
        $editorial = $request->editorial;
        if($editorial == 'OMEGA BOOK') $editorial = 'MAJESTIC EDUCATION';
        $libros = Libro::where('ISBN','like','%'.$isbn.'%')
                    ->where('editorial', $editorial)
                    ->where('estado', 'activo')
                    ->orderBy('ISBN', 'asc')->get();
        return response()->json($libros);
    }

    public function assign_registers($libro){
        return $datos = [
                'id' => $libro->id,
                'ISBN' => $libro->ISBN,
                'titulo' => $libro->titulo,
                'editorial' => $libro->editorial,
                'piezas' => $libro->piezas,
                'defectuosos' => $libro->defectuosos,
                'costo_unitario' => 0,
                'unidades' => 0,
                'total' => 0,
            ];
    }

    // MOSTRAR LIBROS POR COINCIDENCIA DE LETRAS
    // Función utilizada en
    // - AdeudosComponent - DevoluciónAdeudosComponent - EntradasComponent
    // - NewNotaComponent - PromocionesComponent - RemisionComponent - LibrosComponent
    public function buscar(Request $request){
        $queryTitulo = $request->queryTitulo;
        $libros = \DB::table('libros')
                    ->where('titulo','like','%'.$queryTitulo.'%')
                    ->where('estado', 'activo')
                    ->orderBy('type', 'desc')
                    ->orderBy('titulo', 'asc')->get();
        return response()->json($libros);
    }

    public function by_titulo_editorial(Request $request){
        $libros = \DB::table('libros')
                    ->select('id', 'ISBN', 'titulo', 'editorial', 'piezas', 'defectuosos')
                    ->where('editorial', $request->editorial)
                    ->where('titulo','like','%'.$request->titulo.'%')
                    ->where('estado', 'activo')
                    ->orderBy('titulo', 'asc')->get();
        return response()->json($libros);
    }

    public function by_editorial_digital(Request $request){
        $libros = Libro::where('editorial', $request->editorial)
                    ->where('type', $request->type)
                    ->where('titulo','like','%'.$request->titulo.'%')
                    ->where('estado', 'activo')
                    ->orderBy('titulo', 'asc')->get();
        return response()->json($libros);
    }

    public function by_editorial_type_titulo(Request $request){
        $editorial = $request->editorial;
        if($editorial == 'OMEGA BOOK') $editorial = 'MAJESTIC EDUCATION';
        $libros = Libro::where('editorial', $editorial)
                    ->whereNotIn('type', [$request->typeNot])
                    ->where('titulo','like','%'.$request->titulo.'%')
                    ->where('estado', 'activo')
                    ->orderBy('titulo', 'asc')->get();
        return response()->json($libros);
    }

    public function by_editorial_type_isbn(Request $request){
        $editorial = $request->editorial;
        if($editorial == 'OMEGA BOOK') $editorial = 'MAJESTIC EDUCATION';
        $libros = Libro::where('editorial', $editorial)
                    ->whereNotIn('type', [$request->typeNot])
                    ->where('ISBN','like','%'.$request->isbn.'%')
                    ->where('estado', 'activo')
                    ->orderBy('titulo', 'asc')->get();
        return response()->json($libros);
    }

    public function libros_por_editorial(Request $request){
        $queryTitulo = $request->queryTitulo;
        $editorial = $request->editorial;
        if($editorial == 'OMEGA BOOK') $editorial = 'MAJESTIC EDUCATION';
        $libros = \DB::table('libros')
                    ->select('id', 'ISBN', 'titulo', 'editorial', 'piezas')
                    ->where('editorial','like','%'.$editorial.'%')
                    ->where('titulo','like','%'.$queryTitulo.'%')
                    ->where('estado', 'activo')
                    ->orderBy('editorial', 'asc')->get();
        return response()->json($libros);
    }

    // DESCARGAR EN FORMATO EXCEL LOS LIBROS
    // Función utilizada en LibrosComponent
    public function download_list_libros($editorial, $serie_id, $titulo, $isbn, $type){
        $hoy = Carbon::now();
        return Excel::download(new LibrosExport($editorial, $serie_id, $titulo, $isbn, $type), $hoy->format('Y-m-d').'_INVENTARIO-INDIVIDUAL.xlsx');
    }

    // GUARDAR NUEVO LIBRO
    // Función utilizada en NewLibroComponent
    public function store(Request $request){
        $this->func_validar($request);
        \DB::beginTransaction();
        try {
            $serie_id = $request->serie['id'];
            $serie_name = strtoupper($request->serie['serie']);
            if($serie_id == null){
                $serie = Serie::create(['serie' => $serie_name]);
                $serie_id = $serie->id;
            }
            $libro = Libro::create($this->params_libro($request, false, $serie_id));

            if($request->editorial == 'MAJESTIC EDUCATION'){
                $o_serie = Serie::on('opuesto')->where('serie', $serie_name)->first();
                if($o_serie == null) $o_serie = Serie::on('opuesto')->create(['serie' => $serie_name]);

                $l = Libro::on('opuesto')->create($this->params_libro($request, true, $o_serie->id));
                // ESTO ERA PARA AGREGAR EL LIBRO A QUERETARO, YA NO SE UTILIZARA
                // if($request->type != 'digital'){
                //     \DB::connection('majesticeducation')->table('libros')
                //         ->insert([
                //             'ISBN' => $request->ISBN,  
                //             'titulo' => $request->titulo, 
                //             'editorial' => 'MAJESTIC EDUCATION',
                //             'type' => $request->type
                //         ]);
                // }
            }

            $reporte = 'creo el libro '.$libro->type.' '.$libro->ISBN.' / '.$libro->titulo.' de '.$libro->editorial;
            $this->create_report($libro->id, $reporte);

            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json($this->assign_datos_libro($libro, $serie_name));
    }

    public function params_libro($request, $externo, $serie_id){
        return [
            'serie_id' => $serie_id,
            'type' => $request->type,
            'ISBN' => $request->ISBN,
            'titulo' => strtoupper($request->titulo),
            'autor' => strtoupper($request->autor),
            'editorial' => $request->editorial,
            'externo' => $externo
        ];
    }

    //Función para validar los libros
    public function func_validar($request){
        $this->validate($request, [
            'serie.id' => 'min:0|numeric',
            'serie.serie' => 'min:5|required|string',
            'type' => 'required',
            'titulo' => 'min:5|max:100|required|string|unique:libros',
            'ISBN' => 'required|string|max:20|min:10|unique:libros',
            'editorial' => 'required|min:5|max:100|string',
            'defectuosos' => 'numeric|min:0'
        ]);
    }

    // ACTUALIZAR DATOS DE LIBRO
    // Función utilizada en AddEditLibroComponent
    public function update(Request $request){
        $editorial = $request->editorial;
        $libro = Libro::whereId($request->id)->first();
        $libro_anterior = $libro->editorial.': '.$libro->type.' '.$libro->ISBN.' / '.$libro->titulo;

        $libro->ISBN = 'ISBN-'.$libro->ISBN;
        $libro->titulo = 'TITLE-'.$libro->titulo;
        $libro->save();     
        $this->func_validar($request);  

        \DB::beginTransaction();
        try {
            $datos = [
                'type' => $request->type,
                'ISBN' => strtoupper($request->ISBN),
                'titulo' => strtoupper($request->titulo),
                'autor' => strtoupper($request->autor),
                'editorial' => $editorial,
                'updated_at' => Carbon::now()
            ];

            $libro->update(array_merge(['serie_id' => (int)$request->serie['id']], $datos));

            if($editorial == 'MAJESTIC EDUCATION'){
                $o_serie = Serie::on('opuesto')->where('serie', $request->serie['serie'])->first();
                $libro_opuesto = Libro::on('opuesto')->where('titulo', $libro->titulo)->first();
                $libro_opuesto->update(array_merge(['serie_id' => $o_serie->id], $datos));
            }

            $libro_nuevo = $libro->editorial.': '.$libro->type.' '.$libro->ISBN.' / '.$libro->titulo;
            $reporte = 'edito el libro '.$libro_anterior.' a '.$libro_nuevo;
            $this->create_report($libro->id, $reporte);

            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json($this->assign_datos_libro($libro, $libro->serie->serie));
    }

    public function save_defectuosos(Request $request){
        $libro = Libro::whereId($request->id)->first();
        \DB::beginTransaction();
        try {
            $defectuosos = (int) $request->defectuosos;
            $libro->update([
                'defectuosos' => $libro->defectuosos + $defectuosos,
                'piezas' => $libro->piezas - $defectuosos
            ]);

            Defectuoso::create([
                'libro_id' => $libro->id, 
                'numero' => $defectuosos, 
                'comentario' => 'Ingresado por '.auth()->user()->name.': '.$request->motivo
            ]);

            $reporte = 'registro la salida (libros defectuosos) de '.$defectuosos.' unidades - '.$libro->editorial.': '.$libro->type.' '.$libro->ISBN.' / '.$libro->titulo;
            $this->create_report($libro->id, $reporte);

            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json($libro);
    }

    // ELIMINAR LIBRO (FUNCIÓN ELIMINADA DE COMPONENT)
    public function delete(Request $request){
        $id = $request->id;
        
        try {
            \DB::beginTransaction();
            $libro = Libro::whereId($id)->delete();
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json(null, 200);
    }

    // MOSTRAR MOVIMIENTOS DE UN LIBRO
    public function movimientos_todos(){
        $libros = $this->get_libros();
        $movimientos = $this->busqueda_unidades($libros);
        return response()->json($movimientos);
    }

    public function movimientos_libro(Request $request){
        $libros = Libro::where('id', $request->libro_id)->get();
        $movimientos = $this->busqueda_unidades($libros);
        return response()->json($movimientos);
    }

    public function get_libros(){
        $libros = \DB::table('libros')->orderBy('titulo', 'asc')->get();
        return $libros;
    }

    public function get_libros_editorial($editorial){
        $libros = \DB::table('libros')
                ->where('editorial', $editorial)
                ->orderBy('titulo', 'asc')->get();
        return $libros;
    }

    public function busqueda_unidades($libros){
        // ENTRADAS
        // EXCLUIR REGISTROS QUE NO SON TIPO ALUMNO
        $code_registro = \DB::table('code_registro')
                            ->select('registro_id')
                            ->join('codes', 'code_registro.code_id', '=', 'codes.id')
                            ->where('codes.tipo', '!=', 'alumno')
                            ->groupBy('registro_id')
                            ->get();
        $entradas = \DB::table('registros')
                            // ->where('registros.created_at','like', '%2022-12%')
                            ->whereNotIn('id', $code_registro->pluck('registro_id'))
                            ->whereNull('registros.deleted_at')
                            ->select('libro_id as libro_id', \DB::raw('SUM(unidades) as entradas'))
                            ->groupBy('libro_id')
                            ->get(); 
        $devoluciones = \DB::table('fechas')
                            // ->where('fechas.created_at','like', '%2022-12%')
                            ->join('remisiones', 'fechas.remisione_id', '=', 'remisiones.id')
                            ->whereNotIn('remisiones.corte_id', [4])
                            ->whereNull('fechas.deleted_at')
                            ->select('libro_id as libro_id' ,\DB::raw('SUM(unidades) as devoluciones'))
                            ->groupBy('libro_id')
                            ->get();
        $saldevoluciones = \DB::table('saldevoluciones')
                    // ->where('saldevoluciones.created_at','like', '%2022-12%')
                    ->select('libro_id as libro_id' ,\DB::raw('SUM(unidades) as devoluciones'))
                    ->groupBy('libro_id')
                    ->get();
        $prodevoluciones = \DB::table('prodevoluciones')
                    // ->where('prodevoluciones.created_at','like', '%2022-12%')
                    ->select('libro_id as libro_id' ,\DB::raw('SUM(unidades) as devoluciones'))
                    ->groupBy('libro_id')
                    ->get();
        // SALIDAS
        $salidas = \DB::table('sregistros')
                    // ->where('sregistros.created_at','like', '%2022-12%')
                    ->join('salidas', 'sregistros.salida_id', '=', 'salidas.id')
                    ->where('salidas.estado', 'enviado')
                    ->select('libro_id as libro_id' ,\DB::raw('SUM(sregistros.unidades) as salidas'))
                    ->groupBy('libro_id')
                    ->get();
        $entdevoluciones = \DB::table('entdevoluciones')
                    // ->where('entdevoluciones.created_at','like', '%2022-12%')
                    ->join('registros', 'entdevoluciones.registro_id', 'registros.id')
                    ->select('registros.libro_id as libro_id' ,\DB::raw('SUM(entdevoluciones.unidades) as entdevoluciones'))
                    ->groupBy('registros.libro_id')
                    ->get();
        $remisiones = \DB::table('datos')
                    // ->where('datos.created_at','like', '%2022-12%')
                    ->join('remisiones', 'datos.remisione_id', '=', 'remisiones.id')
                    ->whereNotIn('remisiones.estado', ['Cancelado'])
                    ->whereNotIn('remisiones.corte_id', [4])
                    ->whereNull('datos.deleted_at')
                    ->select('libro_id as libro_id' ,\DB::raw('SUM(unidades) as remisiones'))
                    ->groupBy('libro_id')
                    ->get();
        $notas = \DB::table('registers')
                    // ->where('registers.created_at','like', '%2022-12%')
                    ->select('libro_id as libro_id' ,\DB::raw('SUM(unidades) as notas'))
                    ->groupBy('libro_id')
                    ->get();
        // EXCLUIR TODAS LAS PROMOCIONES DE LIBROS DIGITALES QUE NO SON TIPO ALUMNO
        $code_departure = \DB::table('code_departure')
                    ->select('departure_id')
                    ->groupBy('departure_id')
                    ->get();
        $promociones = \DB::table('departures')
                    // ->where('departures.created_at','like', '%2022-12%')
                    ->join('promotions', 'departures.promotion_id', '=', 'promotions.id')
                    ->whereNotIn('promotions.estado', ['Cancelado'])
                    ->whereNotIn('departures.id', $code_departure->pluck('departure_id'))
                    ->select('libro_id as libro_id' ,\DB::raw('SUM(departures.unidades) as promociones'))
                    ->groupBy('libro_id')
                    ->get();
        $donaciones = \DB::table('donaciones')
                    // ->where('donaciones.created_at','like', '%2022-12%')
                    ->select('libro_id as libro_id' ,\DB::raw('SUM(unidades) as donaciones'))
                    ->groupBy('libro_id')
                    ->get();

        $movimientos = array();
        foreach($libros as $libro){
            $relacion = $this->assign_array($libro, $entradas, $saldevoluciones, $prodevoluciones, $devoluciones, $salidas, $entdevoluciones, $remisiones, $notas, $promociones, $donaciones);
            array_push($movimientos, $relacion);
        }   
        return $movimientos;
    }

    public function assign_array($libro, $entradas, $saldevoluciones, $prodevoluciones, $devoluciones, $salidas, $entdevoluciones, $remisiones, $notas, $promociones, $donaciones){
        $relacion = [
            'id' => 0,
            'editorial' => '',
            'ISBN' =>'',
            'libro' => '',
            'entradas' => 0,
            'devoluciones' => 0,
            'saldevoluciones' => 0,
            'prodevoluciones' => 0,
            'salidas' => 0,
            'entdevoluciones' => 0,
            'remisiones' => 0,
            'notas' => 0,
            'promociones' => 0,
            'donaciones' => 0,
            'defectuosos' => 0,
            'existencia' => 0,
        ];
        $relacion['existencia'] = $libro->piezas;
        $relacion['id'] = $libro->id;
        $relacion['editorial'] = $libro->editorial;
        $relacion['ISBN'] = $libro->ISBN;
        $relacion['libro'] = $libro->titulo;
        $relacion['defectuosos'] = $libro->defectuosos;
        // ENTRADAS
        foreach($entradas as $entrada){
            if($libro->id === $entrada->libro_id)
                $relacion['entradas'] = $entrada->entradas;
        }
        foreach($devoluciones as $devolucion){
            if($libro->id === $devolucion->libro_id)
                $relacion['devoluciones'] = $devolucion->devoluciones;
        }
        foreach($saldevoluciones as $devolucion){
            if($libro->id === $devolucion->libro_id)
                $relacion['saldevoluciones'] = $devolucion->devoluciones;
        }
        foreach($prodevoluciones as $devolucion){
            if($libro->id === $devolucion->libro_id)
                $relacion['prodevoluciones'] = $devolucion->devoluciones;
        }
        foreach($salidas as $salida){
            if($libro->id === $salida->libro_id)
                $relacion['salidas'] = $salida->salidas;
        }
        foreach($entdevoluciones as $entdevolucion){
            if($libro->id === $entdevolucion->libro_id)
                $relacion['entdevoluciones'] = $entdevolucion->entdevoluciones;
        }
        foreach($remisiones as $remision){
            if($libro->id === $remision->libro_id)
                $relacion['remisiones'] = $remision->remisiones;
        }
        foreach($notas as $nota){
            if($libro->id === $nota->libro_id)
                $relacion['notas'] = $nota->notas;
        }
        foreach($promociones as $promocion){
            if($libro->id === $promocion->libro_id)
                $relacion['promociones'] = $promocion->promociones;
        }
        foreach($donaciones as $donacion){
            if($libro->id === $donacion->libro_id)
                $relacion['donaciones'] = $donacion->donaciones;
        }
        return $relacion;
    }

    public function busqueda_monto_gral(){
        $registros = \DB::table('registros')
                ->join('libros', 'registros.libro_id', '=', 'libros.id')
                ->select(
                    'libro_id as libro_id',
                    'libros.titulo as libro',
                    \DB::raw('SUM(total) as entradas')
                )->groupBy('libro_id', 'libros.titulo')
                ->orderBy('libros.titulo', 'asc')
                ->get();
        return $registros;
    }

    public function monto_editorial_gral($editorial){
        $registros = \DB::table('registros')
            ->join('libros', 'registros.libro_id', '=', 'libros.id')
            ->where('libros.editorial', $editorial)
            ->select(
                'libro_id as libro_id',
                'libros.titulo as libro',
                \DB::raw('SUM(total) as entradas')
            )->groupBy('libro_id', 'libros.titulo')
            ->orderBy('libros.titulo', 'asc')
            ->get();
        return $registros;
    }

    

    public function movimientos_por_edit(Request $request){
        $editorial = $request->queryEMov;
        if($editorial === 'TODO'){
            $registros = $this->get_libros();
        } else{
            $registros = $this->get_libros_editorial($editorial);
        }
        $movimientos = $this->busqueda_unidades($registros);
        return response()->json($movimientos);
    }

    public function down_movgral($editorial, $type){
        return Excel::download(new MovLibrosExport($editorial, $type), 'movimientos-libros.xlsx');
    }

    public function down_fechaCategoria($inicio, $final, $categoria){
        return Excel::download(new MovFechasExport($inicio, $final, $categoria), $categoria.'.xlsx');
    }

    public function movimientos_por_fecha(Request $request){
        $categoria = $request->categoria;
        $inicio = $request->inicio;
        $final = $request->final;

        $fechas = $this->format_date($inicio, $final);
        $fecha1 = $fechas['inicio'];
        $fecha2 = $fechas['final'];

        // ENTRADAS
        if($categoria === 'ENTRADAS'){
            $datos = \DB::table('registros')
                ->join('libros', 'registros.libro_id', '=', 'libros.id')
                ->whereBetween('registros.created_at', [$fecha1, $fecha2])
                ->select(
                    // 'libro_id as libro_id',
                    'libros.titulo as libro',
                    \DB::raw('SUM(unidades) as unidades'),
                    \DB::raw('SUM(total) as total')
                )->groupBy('libro_id', 'libros.titulo')
                ->orderBy('libros.titulo', 'asc')
                ->get();
        }
        if($categoria === 'DEVOLUCIONES'){
            $datos = \DB::table('devoluciones')
                ->join('libros', 'devoluciones.libro_id', '=', 'libros.id')
                ->whereBetween('devoluciones.created_at', [$fecha1, $fecha2])
                ->whereNotIn('devoluciones.unidades', [0])
                ->select(
                    'libros.titulo as libro',
                    \DB::raw('SUM(unidades) as unidades'),
                    \DB::raw('SUM(total) as total')
                )
                ->orderBy('libros.titulo', 'asc')
                ->groupBy('libro_id', 'libros.titulo')
                ->get();
        }
        if($categoria === 'NOTASDEV'){
            $datos = \DB::table('registers')
                    ->join('libros', 'registers.libro_id', '=', 'libros.id')
                    ->whereNotIn('registers.unidades_devuelto', [0])
                    ->select(
                        'libros.titulo as libro',
                        \DB::raw('SUM(unidades_devuelto) as unidades'),
                        \DB::raw('SUM(total_devuelto) as total')
                    )->whereBetween('registers.created_at', [$fecha1, $fecha2])
                    ->orderBy('libros.titulo', 'asc')
                    ->groupBy('libro_id', 'libros.titulo')
                    ->get();
        }
        // SALIDAS
        if($categoria === 'REMISIONES'){
            $datos = \DB::table('datos')
                    ->join('remisiones', 'datos.remisione_id', '=', 'remisiones.id')
                    ->join('libros', 'datos.libro_id', '=', 'libros.id')
                    ->whereNotIn('remisiones.estado', ['Cancelado'])
                    ->whereBetween('datos.created_at', [$fecha1, $fecha2])
                    ->select(
                        // 'libro_id as libro_id',
                        'libros.titulo as libro',
                        \DB::raw('SUM(datos.unidades) as unidades'),
                        \DB::raw('SUM(datos.total) as total')
                    )
                    ->orderBy('libros.titulo', 'asc')
                    ->groupBy('libro_id', 'libros.titulo')
                    ->get();
        }
        if($categoria === 'NOTAS'){
            $datos = \DB::table('registers')
                    ->join('libros', 'registers.libro_id', '=', 'libros.id')
                    ->select(
                        'libros.titulo as libro',
                        \DB::raw('SUM(unidades) as unidades'),
                        \DB::raw('SUM(total) as total')
                    )->whereBetween('registers.created_at', [$fecha1, $fecha2])
                    ->orderBy('libros.titulo', 'asc')
                    ->groupBy('libro_id', 'libros.titulo')
                    ->get();
        }
        if($categoria === 'PEDIDOS'){
            // $datos = \DB::table('pedidos')
            //         ->join('libros', 'pedidos.libro_id', '=', 'libros.id')
            //         ->select(
            //             // 'libro_id as libro_id',
            //             'libros.titulo as libro',
            //             \DB::raw('SUM(unidades) as unidades'),
            //             \DB::raw('SUM(total) as total')
            //         )->whereBetween('pedidos.created_at', [$fecha1, $fecha2])
            //         ->orderBy('libros.titulo', 'asc')
            //         ->groupBy('libro_id', 'libros.titulo')
            //         ->get();
        }
        if($categoria === 'PROMOCIONES'){
            $datos = \DB::table('departures')
                    ->join('libros', 'departures.libro_id', '=', 'libros.id')
                    ->select('libros.titulo as libro', \DB::raw('SUM(unidades) as unidades'))
                    ->whereBetween('departures.created_at', [$fecha1, $fecha2])
                    ->orderBy('libros.titulo', 'asc')
                    ->groupBy('libro_id', 'libros.titulo')
                    ->get();
        }
        if($categoria === 'DONACIONES'){
            $datos = \DB::table('donaciones')
                    ->join('libros', 'donaciones.libro_id', '=', 'libros.id')
                    ->select('libros.titulo as libro', \DB::raw('SUM(unidades) as unidades'))
                    ->whereBetween('donaciones.created_at', [$fecha1, $fecha2])
                    ->orderBy('libros.titulo', 'asc')
                    ->groupBy('libro_id', 'libros.titulo')
                    ->get();
        }
        return response()->json($datos);
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

    public function detalles_movimientos(Request $request){
        $titulo = $request->titulo;
        $libro = Libro::where('titulo', $titulo)
                    ->with(['registros.entrada', 'registros.entdevoluciones.entrada','registers.note', 'departures.promotion', 'donaciones.regalo', 'saldevoluciones.salida'])
                    ->first();
        $datos = \DB::table('datos')
                    ->join('remisiones', 'datos.remisione_id', '=', 'remisiones.id')
                    ->where('libro_id', $libro->id)
                    ->whereNotIn('remisiones.estado', ['Cancelado'])
                    ->whereNotIn('remisiones.corte_id', [4])
                    ->whereNull('datos.deleted_at')
                    ->select('remisiones.id as remisione_id', 'unidades')
                    ->get();
        $devoluciones = Devolucione::where('libro_id', $libro->id)
                        ->whereNotIn('unidades', [0])
                        ->with('remisione')->get();
        $prodevoluciones = \DB::table('prodevoluciones')
                    ->join('promotions', 'prodevoluciones.promotion_id', '=', 'promotions.id')
                    ->where('libro_id', $libro->id)
                    ->whereNotIn('promotions.estado', ['Cancelado'])
                    ->select('promotions.folio as folio', 'prodevoluciones.unidades')
                    ->get();
        $salidas = \DB::table('sregistros')
                    ->join('salidas', 'sregistros.salida_id', '=', 'salidas.id')
                    ->where('libro_id', $libro->id)
                    ->where('salidas.estado', 'enviado')
                    ->select('salidas.folio as folio', 'sregistros.unidades as unidades')
                    ->get();
        return response()->json([
                    'libro' => $libro, 
                    'datos' => $datos, 
                    'devoluciones' => $devoluciones, 
                    'salidas' => $salidas, 
                    'prodevoluciones' => $prodevoluciones
                ]);
    }

    public function all_movmonto(){
        $libros = $this->get_libros();
        $movimientos = $this->busqueda_monto($libros);
        $registros = $this->assign_mov($movimientos);
        return response()->json($registros);
    }

    public function editorial_movmonto(Request $request){
        $editorial = $request->editorial;
        if($editorial === 'TODO'){
            $libros = $this->get_libros();
        } else{
            $libros = $this->get_libros_editorial($editorial);
        }
        $movimientos = $this->busqueda_monto($libros);
        $registros = $this->assign_mov($movimientos);
        return response()->json($registros);
    }

    public function fecha_movmonto(Request $request){
        $editorial = $request->editorial;
        $mes = $request->mes;

        $año = Carbon::now()->format('Y');
        $fecha = $año.'-'.$mes;

        if($editorial === 'TODO'){
            $libros = $this->get_libros();
        } else{
            $libros = $this->get_libros_editorial($editorial);
        }
        
        $movimientos = $this->busqueda_fecha_monto($libros, $fecha);
        $registros = $this->assign_mov($movimientos);
        return response()->json($registros);
    }

    public function assign_mov($movimientos){
        $registros = array();
        foreach($movimientos as $m){
            if($m['total_entrada'] > 0 || $m['total_salida'] > 0)
                array_push($registros, $m);
        }
        return $registros;
    }

    public function detalles_monto(Request $request){
        $titulo = $request->titulo;
        $libro = Libro::where('titulo', $titulo)->first();
        $datos = $this->busqueda_por_libro($libro);
        return response()->json($datos);
    }

    public function busqueda_por_libro($libro){
        // ENTRADAS 
        // (ENTRADAS)
        $entradas = \DB::table('registros')
                ->join('entradas', 'registros.entrada_id', '=', 'entradas.id')
                ->select('entradas.folio as folio', 'entradas.editorial as editorial', 'registros.total as entradas')
                ->where('libro_id', $libro->id)
                ->where('registros.total', '>', 0)
                ->get();
        // (DEVOLUCIONES)
        $devoluciones = \DB::table('devoluciones')
                ->join('remisiones', 'devoluciones.remisione_id', '=', 'remisiones.id')
                ->join('clientes', 'remisiones.cliente_id', '=', 'clientes.id')
                ->select('remisiones.id as folio', 'clientes.name as cliente', 'devoluciones.total as devoluciones')
                ->where('libro_id', $libro->id)
                ->where('devoluciones.total', '>', 0)
                ->get();
        // SALIDAS
        // (REMISIONES)
        $remisiones = \DB::table('datos')
                ->join('remisiones', 'datos.remisione_id', '=', 'remisiones.id')
                ->join('clientes', 'remisiones.cliente_id', '=', 'clientes.id')
                ->where('datos.libro_id', $libro->id)
                ->whereNotIn('remisiones.estado', ['Cancelado'])
                ->whereNull('datos.deleted_at')
                ->select('remisiones.id as folio', 'clientes.name as cliente', 'datos.total as remisiones')
                ->get();
        // (SALIDA)
        $notas = \DB::table('registers')
                ->join('notes', 'registers.note_id', '=', 'notes.id')
                ->where('libro_id', $libro->id)
                ->select('notes.folio as folio', 'notes.cliente as cliente', 'registers.total as notas')
                ->get();
        // (DEVOLUCIONES ENTRADA)
        $entdevoluciones = \DB::table('entdevoluciones')
                ->join('entradas', 'entdevoluciones.entrada_id', '=', 'entradas.id')
                ->join('registros', 'entdevoluciones.registro_id', 'registros.id')
                ->where('registros.libro_id', $libro->id)
                ->select('entradas.folio as folio', 'entradas.editorial as editorial', 'entdevoluciones.total as entdevoluciones')
                ->get();
        $datos = [
            'libro' => $libro->titulo,
            'entradas' => $entradas,
            'devoluciones' => $devoluciones,
            'remisiones' => $remisiones,
            'notas' => $notas,
            'entdevoluciones' => $entdevoluciones
        ];
        return $datos;
    }

    public function busqueda_monto($libros){
        // ENTRADAS 
        // (ENTRADAS)
        $entradas = \DB::table('registros')
                ->select('libro_id as libro_id' ,\DB::raw('SUM(total) as entradas'))
                ->where('total', '>', 0) 
                ->groupBy('libro_id')
                ->get();
        // (DEVOLUCIONES)
        $devoluciones = \DB::table('devoluciones')
                ->select('libro_id as libro_id' ,\DB::raw('SUM(total) as devoluciones'))
                ->where('total', '>', 0) 
                ->groupBy('libro_id')
                ->get();
        // SALIDAS
        // (REMISIONES)
        $remisiones = \DB::table('datos')
                ->join('remisiones', 'datos.remisione_id', '=', 'remisiones.id')
                ->whereNotIn('remisiones.estado', ['Cancelado'])
                ->whereNull('datos.deleted_at')
                ->select('libro_id as libro_id', \DB::raw('SUM(datos.total) as remisiones'))
                ->groupBy('libro_id')
                ->get();
        // (SALIDA)
        $notas = \DB::table('registers')
                ->select('libro_id as libro_id' ,\DB::raw('SUM(total) as notas'))
                ->groupBy('libro_id')
                ->get();
        // (DEVOLUCIONES ENTRADA)
        $entdevoluciones = \DB::table('entdevoluciones')
                ->join('registros', 'entdevoluciones.registro_id', 'registros.id')
                ->select('registros.libro_id as libro_id' ,\DB::raw('SUM(entdevoluciones.total) as entdevoluciones'))
                ->groupBy('registros.libro_id')
                ->get();
        $movimientos = array();
        foreach($libros as $libro){
            $relacion = $this->assignMonto($libro, $entradas, $devoluciones, $entdevoluciones, $remisiones, $notas);
            array_push($movimientos, $relacion);
        }   
        return $movimientos;
    }

    // Mostrar movimientos por fecha
    public function busqueda_fecha_monto($libros, $fecha){
        // ENTRADAS 
        // (ENTRADAS)
        $entradas = \DB::table('registros')
                ->select('libro_id as libro_id' ,\DB::raw('SUM(total) as entradas'))
                ->where('total', '>', 0) 
                ->where('created_at', 'like', '%'.$fecha.'%')
                ->groupBy('libro_id')
                ->get();
        // (DEVOLUCIONES)
        $devoluciones = \DB::table('fechas')
                ->select('libro_id as libro_id' ,\DB::raw('SUM(total) as devoluciones'))
                ->where('total', '>', 0)
                ->where('created_at', 'like', '%'.$fecha.'%')
                ->groupBy('libro_id')
                ->get();
        // SALIDAS
        // (REMISIONES)
        $remisiones = \DB::table('datos')
                ->join('remisiones', 'datos.remisione_id', '=', 'remisiones.id')
                ->whereNotIn('remisiones.estado', ['Cancelado'])
                ->whereNull('datos.deleted_at')
                ->where('datos.created_at', 'like', '%'.$fecha.'%')
                ->select('libro_id as libro_id', \DB::raw('SUM(datos.total) as remisiones'))
                ->groupBy('libro_id')
                ->get();
        // (SALIDA)
        $notas = \DB::table('registers')
                ->select('libro_id as libro_id' ,\DB::raw('SUM(total) as notas'))
                ->where('created_at', 'like', '%'.$fecha.'%')
                ->groupBy('libro_id')
                ->get();
        // (DEVOLUCIONES ENTRADA)
        $entdevoluciones = \DB::table('entdevoluciones')
                ->join('registros', 'entdevoluciones.registro_id', 'registros.id')
                ->select('registros.libro_id as libro_id' ,\DB::raw('SUM(entdevoluciones.total) as entdevoluciones'))
                ->where('entdevoluciones.created_at', 'like', '%'.$fecha.'%')
                ->groupBy('registros.libro_id')
                ->get();
        $movimientos = array();
        foreach($libros as $libro){
            $relacion = $this->assignMonto($libro, $entradas, $devoluciones, $entdevoluciones, $remisiones, $notas);
            array_push($movimientos, $relacion);
        }   
        return $movimientos;
    } 

    public function assignMonto($libro, $entradas, $devoluciones, $entdevoluciones, $remisiones, $notas){
        $relacion = [
            'titulo' => '',
            'entradas' => 0,
            'devoluciones' => 0,
            'total_entrada' => 0,
            'remisiones' => 0,
            'notas' => 0,
            'entdevoluciones' => 0,
            'total_salida' => 0,
            'total' => 0,
            '_cellVariants' => [ 'total' => '' ]
        ];
        $relacion['titulo'] = $libro->titulo;
        foreach($entradas as $entrada){
            if($libro->id === $entrada->libro_id){
                $relacion['entradas'] = $entrada->entradas;
                $relacion['total_entrada'] += $entrada->entradas;
            }
        }
        foreach($devoluciones as $devolucion){
            if($libro->id === $devolucion->libro_id){
                $relacion['devoluciones'] = $devolucion->devoluciones;
                $relacion['total_entrada'] += $devolucion->devoluciones;
            }
        }
        foreach($entdevoluciones as $entdevolucion){
            if($libro->id === $entdevolucion->libro_id){
                $relacion['entdevoluciones'] = $entdevolucion->entdevoluciones;
                $relacion['total_salida'] += $entdevolucion->entdevoluciones;
            }
        }
        foreach($remisiones as $remision){
            if($libro->id === $remision->libro_id){
                $relacion['remisiones'] = $remision->remisiones;
                $relacion['total_salida'] += $remision->remisiones;
            }
        }
        foreach($notas as $nota){
            if($libro->id === $nota->libro_id){
                $relacion['notas'] = $nota->notas;
                $relacion['total_salida'] += $nota->notas;
            }
        }
        $total = $relacion['total_salida'] - $relacion['total_entrada'];
        $relacion['total'] = $total;
        $variant = '';
        if($relacion['entradas'] > 0){
            if($relacion['total_salida'] > $relacion['total_entrada']) $variant  = 'success';
            if($relacion['total_salida'] == $relacion['total_entrada']) $variant = 'warning';
            if($relacion['total_salida'] < $relacion['total_entrada']) $variant = 'danger';
        }
        $relacion['_cellVariants']['total'] = $variant;
        return $relacion;
    }

    public function download_movmonto($editorial, $mes){
        return Excel::download(new MovMontoExport($editorial, $mes), 'movimientos-monto.xlsx');
    }

    // MARCAR COMO INACTIVO EL LIBRO
    public function inactivar(Request $request){
        \DB::beginTransaction();
        try {
            $libro = Libro::find($request->libro_id);
            $libro->update([
                'estado' => 'inactivo'
            ]);

            $reporte = 'desactivo el libro '.$libro->editorial.': '.$libro->type.' '.$libro->ISBN.' / '.$libro->titulo;
            $this->create_report($libro->id, $reporte);

            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json(true);
    }

    // OBTENER ENTRADAS Y SALIDAS
    public function entradas_salidas(Request $request){
        $inicio = $request->de.' 00:00:00';
        $final = $request->a.' 23:59:59';
        $editorial = $request->editorial;
        $libro_id = $request->libro_id;

        // ENTRADAS
        $entradas = \DB::table('registros')
                    ->join('entradas', 'registros.entrada_id', '=', 'entradas.id')
                    ->join('libros', 'registros.libro_id', '=', 'libros.id')
                    ->whereBetween('entradas.created_at', [$inicio, $final])
                    ->whereNull('registros.deleted_at')
                    ->select('registros.libro_id', 'registros.unidades')
                    ->get();
        $fechas = \DB::table('fechas')
                    ->join('libros', 'fechas.libro_id', '=', 'libros.id')
                    ->whereBetween('fechas.fecha_devolucion', [$inicio, $final])
                    ->whereNull('fechas.deleted_at')
                    ->select('fechas.libro_id', 'fechas.unidades')
                    ->get();
        $saldevoluciones = \DB::table('saldevoluciones')
                    ->whereBetween('saldevoluciones.created_at', [$inicio, $final])
                    ->select('saldevoluciones.libro_id', 'saldevoluciones.unidades')
                    ->get();
        $prodevoluciones = \DB::table('prodevoluciones')
                    ->whereBetween('prodevoluciones.created_at', [$inicio, $final])
                    ->select('prodevoluciones.libro_id', 'prodevoluciones.unidades')
                    ->get();
        
        // SALIDAS
        $salidas = \DB::table('sregistros')
                    ->join('salidas', 'sregistros.salida_id', '=', 'salidas.id')
                    ->where('salidas.estado', 'enviado')
                    ->whereBetween('sregistros.created_at', [$inicio, $final])
                    ->select('libro_id as libro_id', 'sregistros.unidades')
                    ->get();
        $entdevoluciones = \DB::table('entdevoluciones')
                    ->join('registros', 'entdevoluciones.registro_id', '=', 'registros.id')
                    ->join('libros', 'registros.libro_id', '=', 'libros.id')
                    ->whereBetween('entdevoluciones.created_at', [$inicio, $final])
                    ->select('registros.libro_id', 'entdevoluciones.unidades')
                    ->get();
        $remisiones = \DB::table('datos')
                    ->join('remisiones', 'datos.remisione_id', '=', 'remisiones.id')
                    ->join('libros', 'datos.libro_id', '=', 'libros.id')
                    ->whereBetween('remisiones.created_at', [$inicio, $final])
                    ->whereNotIn('remisiones.estado', ['Cancelado'])
                    ->whereNull('datos.deleted_at')
                    ->select('datos.libro_id', 'datos.unidades')
                    ->get();
        $notas = \DB::table('registers')
                    ->join('notes', 'registers.note_id', '=', 'notes.id')
                    ->join('libros', 'registers.libro_id', '=', 'libros.id')
                    ->whereBetween('notes.created_at', [$inicio, $final])
                    ->select('registers.libro_id', 'registers.unidades')
                    ->get();
        $promociones = \DB::table('departures')
                    ->join('promotions', 'departures.promotion_id', '=', 'promotions.id')
                    ->join('libros', 'departures.libro_id', '=', 'libros.id')
                    ->whereBetween('promotions.created_at', [$inicio, $final])
                    ->whereNotIn('promotions.estado', ['Cancelado'])
                    ->select('departures.libro_id', 'departures.unidades')
                    ->get();
        $donaciones = \DB::table('donaciones')
                    ->join('regalos', 'donaciones.regalo_id', '=', 'regalos.id')
                    ->join('libros', 'donaciones.libro_id', '=', 'libros.id')
                    ->whereBetween('regalos.created_at', [$inicio, $final])
                    ->select('donaciones.libro_id', 'donaciones.unidades')
                    ->get();

        $ids = [];
        $ids = $this->get_ids_libros($entradas, $ids);
        $ids = $this->get_ids_libros($fechas, $ids);
        $ids = $this->get_ids_libros($saldevoluciones, $ids);
        $ids = $this->get_ids_libros($prodevoluciones, $ids);
        $ids = $this->get_ids_libros($salidas, $ids);
        $ids = $this->get_ids_libros($entdevoluciones, $ids);
        $ids = $this->get_ids_libros($remisiones, $ids);
        $ids = $this->get_ids_libros($notas, $ids);
        $ids = $this->get_ids_libros($promociones, $ids);
        $ids = $this->get_ids_libros($donaciones, $ids);
        
        $lista_datos = [];

        // BUSCAR POR LIBRO
        if($editorial == null){
            $libros = Libro::where('id', $libro_id)->get();
        }
        // BUSCAR POR EDITORIAL
        if($libro_id == null){
            $libros = Libro::where('editorial', $editorial)->whereIn('id', $ids)->orderBy('titulo', 'asc')->get();
        }

        $libros->map(function($libro) use(&$lista_datos, $entradas, $fechas, $saldevoluciones, $prodevoluciones, $salidas, $entdevoluciones, $remisiones, $notas, $promociones, $donaciones){
            $ter = $this->get_datos_libros($libro->id, $entradas);
            $tdf = $this->get_datos_libros($libro->id, $fechas);
            $tsd = $this->get_datos_libros($libro->id, $saldevoluciones);
            $tdd = $this->get_datos_libros($libro->id, $prodevoluciones);
            $tss = $this->get_datos_libros($libro->id, $salidas);
            $ted = $this->get_datos_libros($libro->id, $entdevoluciones);
            $trr = $this->get_datos_libros($libro->id, $remisiones);
            $tnr = $this->get_datos_libros($libro->id, $notas);
            $tpd = $this->get_datos_libros($libro->id, $promociones);
            $trd = $this->get_datos_libros($libro->id, $donaciones);
            
            $datos = [
                'id' => $libro->id,
                'libro' => $libro->titulo,
                'entradas' => $ter,
                'devoluciones' => $tdf,
                'saldevoluciones' => $tsd,
                'prodevoluciones' => $tdd,
                'salidas' => $tss,
                'entdevoluciones' => $ted,
                'remisiones' => $trr,
                'notas' => $tnr,
                'promociones' => $tpd,
                'donaciones' => $trd,
                '_cellVariants' => [
                    'entradas' => $ter > 0 ? 'success':'',
                    'devoluciones' => $tdf > 0 ? 'success':'',
                    'saldevoluciones' => $tsd > 0 ? 'success':'',
                    'prodevoluciones' => $tdd > 0 ? 'success':'',
                    'salidas' => $tss > 0 ? 'primary':'',
                    'entdevoluciones' => $ted > 0 ? 'primary':'',
                    'remisiones' => $trr > 0 ? 'primary':'',
                    'notas' => $tnr > 0 ? 'primary':'',
                    'promociones' => $tpd > 0 ? 'primary':'',
                    'donaciones' => $trd > 0 ? 'primary':''
                ]
            ];
            
            $lista_datos[] = $datos;
        });
        return response()->json(collect($lista_datos));
    }

    // OBTENER DETALLES
    public function details_entsal(Request $request){
        $inicio = $request->de.' 00:00:00';
        $final = $request->a.' 23:59:59';
        $libro_id = $request->libro_id;
        $libro = Libro::find($libro_id);
        // ENTRADAS
        $entradas = \DB::table('registros') 
                    ->join('entradas', 'registros.entrada_id', '=', 'entradas.id')
                    ->join('libros', 'registros.libro_id', '=', 'libros.id')
                    ->where('registros.libro_id', $libro_id)
                    ->whereBetween('entradas.created_at', [$inicio, $final])
                    ->whereNull('registros.deleted_at')
                    ->select('libros.titulo', 'entradas.folio as folio', 'registros.unidades', 'entradas.created_at')
                    ->get();
        $fechas = \DB::table('fechas')
                    ->join('libros', 'fechas.libro_id', '=', 'libros.id')
                    ->where('fechas.libro_id', $libro_id)
                    ->whereBetween('fechas.fecha_devolucion', [$inicio, $final])
                    ->whereNull('fechas.deleted_at')
                    ->select('libros.titulo', 'fechas.remisione_id as folio', 'fechas.unidades', 'fechas.fecha_devolucion')
                    ->get();
        $saldevoluciones = \DB::table('saldevoluciones')
                    ->join('salidas', 'saldevoluciones.salida_id', '=', 'salidas.id')
                    ->join('libros', 'saldevoluciones.libro_id', '=', 'libros.id')
                    ->where('saldevoluciones.libro_id', $libro_id)
                    ->whereBetween('saldevoluciones.created_at', [$inicio, $final])
                    ->select('libros.titulo', 'salidas.folio as folio', 'saldevoluciones.unidades', 'saldevoluciones.created_at')
                    ->get();
        $prodevoluciones = \DB::table('prodevoluciones')
                    ->join('promotions', 'prodevoluciones.promotion_id', '=', 'promotions.id')
                    ->join('libros', 'prodevoluciones.libro_id', '=', 'libros.id')
                    ->where('libro_id', $libro->id)
                    ->whereBetween('prodevoluciones.created_at', [$inicio, $final])
                    ->select('libros.titulo', 'promotions.folio as folio', 'prodevoluciones.unidades', 'prodevoluciones.created_at')
                    ->get();
        // SALIDAS
        $salidas = \DB::table('sregistros')
                    ->join('salidas', 'sregistros.salida_id', '=', 'salidas.id')
                    ->join('libros', 'sregistros.libro_id', '=', 'libros.id')
                    ->where('sregistros.libro_id', $libro_id)
                    ->where('salidas.estado', 'enviado')
                    ->whereBetween('sregistros.created_at', [$inicio, $final])
                    ->select('libros.titulo', 'salidas.folio as folio', 'sregistros.unidades', 'sregistros.created_at')
                    ->get();
        $entdevoluciones = \DB::table('entdevoluciones')
                    ->join('registros', 'entdevoluciones.registro_id', '=', 'registros.id')
                    ->join('entradas', 'registros.entrada_id', '=', 'entradas.id')
                    ->join('libros', 'registros.libro_id', '=', 'libros.id')
                    ->where('registros.libro_id', $libro_id)
                    ->whereBetween('entdevoluciones.created_at', [$inicio, $final])
                    ->select('libros.titulo', 'entradas.folio as folio', 'entdevoluciones.unidades', 'entdevoluciones.created_at')
                    ->get();
        $remisiones = \DB::table('datos')
                    ->join('remisiones', 'datos.remisione_id', '=', 'remisiones.id')
                    ->join('libros', 'datos.libro_id', '=', 'libros.id')
                    ->where('datos.libro_id', $libro_id)
                    ->whereNotIn('remisiones.estado', ['Cancelado'])
                    ->whereNull('datos.deleted_at')
                    ->whereBetween('remisiones.created_at', [$inicio, $final])
                    ->select('libros.titulo', 'remisiones.id as folio', 'datos.unidades', 'remisiones.created_at')
                    ->get();
        $notas = \DB::table('registers')
                    ->join('notes', 'registers.note_id', '=', 'notes.id')
                    ->join('libros', 'registers.libro_id', '=', 'libros.id')
                    ->where('registers.libro_id', $libro_id)
                    ->whereBetween('notes.created_at', [$inicio, $final])
                    ->select('libros.titulo', 'notes.folio as folio', 'registers.unidades', 'notes.created_at')
                    ->get();
        $promociones = \DB::table('departures')
                    ->join('promotions', 'departures.promotion_id', '=', 'promotions.id')
                    ->join('libros', 'departures.libro_id', '=', 'libros.id')
                    ->where('departures.libro_id', $libro_id)
                    ->whereBetween('promotions.created_at', [$inicio, $final])
                    ->whereNotIn('promotions.estado', ['Cancelado'])
                    ->select('libros.titulo', 'promotions.folio as folio', 'departures.unidades', 'promotions.created_at')
                    ->get();
        $donaciones = \DB::table('donaciones')
                    ->join('regalos', 'donaciones.regalo_id', '=', 'regalos.id')
                    ->join('libros', 'donaciones.libro_id', '=', 'libros.id')
                    ->where('donaciones.libro_id', $libro_id)
                    ->whereBetween('regalos.created_at', [$inicio, $final])
                    ->select('libros.titulo', 'regalos.plantel as folio', 'donaciones.unidades', 'regalos.created_at')
                    ->get();

        $lista_datos = [
            'id' => $libro->id,
            'libro' => $libro->titulo,
            'entradas' => $entradas,
            'devoluciones' => $fechas,
            'saldevoluciones' => $saldevoluciones,
            'prodevoluciones' => $prodevoluciones,
            'salidas' => $salidas,
            'entdevoluciones' => $entdevoluciones,
            'remisiones' => $remisiones,
            'notas' => $notas,
            'promociones' => $promociones,
            'donaciones' => $donaciones,
        ];
        return response()->json($lista_datos);
    }

    public function get_ids_libros($array, $ids){
        $array->map(function($a) use(&$ids){
            $ids[] = $a->libro_id;
        });
        return $ids;
    }

    public function get_datos_libros($libro_id, $array){
        $dato = 0;
        foreach ($array as $a) {
            if($libro_id == $a->libro_id) $dato += $a->unidades;
        }
        return $dato;
    }

    public function get_editoriales(){
        $editoriales = \DB::table('editoriales')->orderBy('editorial', 'asc')->get();
        return response()->json($editoriales);
    }

    public function download_entsal($editorial, $de, $a){
        return Excel::download(new EntSalExport($editorial, $de, $a), 'entradas-salidas.xlsx');
    }

    public function send_movday($de, $a){
        $hoy = Carbon::now();
        // $movimientos = Excel::raw(new MovDayLibrosExport($de, $a), \Maatwebsite\Excel\Excel::XLSX);
        return Excel::download(new MovDayLibrosExport($de, $a), 'entradas-salidas.xlsx');
        // return Excel::download(new LibrosDay($movimientos, $hoy->format('Y-m-d')), 'entradas-salidas.xlsx');
        // Mail::to('g.perez_3@hotmail.com')
        //     ->send(new LibrosDay($movimientos, $hoy->format('Y-m-d')));
        // return response()->json(true);
    }

    public function by_titulo_type(Request $request){
        $libros = Libro::where('type',$request->type)
                    ->where('titulo','like','%'.$request->titulo.'%')
                    ->where('estado', 'activo')
                    ->orderBy('titulo', 'asc')->get();
        return response()->json($libros);
    }

    public function by_titulo_nu(Request $request){
        $cliente_libro = \DB::table('cliente_libro')
                    ->where('cliente_id', $request->cliente_id)->pluck('libro_id');
        $libros = \DB::table('libros')
                    ->select('id', 'type', 'ISBN', 'titulo', 'editorial', 'piezas', 'defectuosos')
                    ->whereNotIn('id', $cliente_libro)
                    ->where('titulo','like','%'.$request->queryTitulo.'%')
                    ->where('estado', 'activo')
                    ->orderBy('titulo', 'asc')->get();
        return response()->json($libros);
    }

    public function create_report($libro_id, $reporte){
        Reporte::create([
            'user_id' => auth()->user()->id, 
            'type' => 'libro', 
            'reporte' => $reporte,
            'name_table' => 'libros', 
            'id_table' => $libro_id
        ]);
    }

    public function all_list(){
        $s1 = Libro::orderBy('editorial', 'asc')
                        ->orderBy('titulo', 'asc')
                        ->where('estado', 'activo')
                        ->where('editorial', 'MAJESTIC EDUCATION')->get();
        $s2 = Libro::on('opuesto')->orderBy('editorial', 'asc')
                        ->orderBy('titulo', 'asc')
                        ->where('estado', 'activo')
                        ->where('editorial', 'MAJESTIC EDUCATION')->get();
        
        $ls = $this->organizar_todo($s1, $s2);
        return response()->json(['libros' => $ls, 'sistema_1' => env('APP_NAME'), 'sistema_2' => env('APP_OPUESTO')]);
    }

    public function organizar_todo($s1, $s2){
        $ls = collect();
        $s1->map(function($libro) use(&$ls, $s2){
            $data1 = $this->get_only_scratch('mysql', $libro->id, $libro->piezas, $libro->type);
            $sum_scratch1 = (int) $data1['sum_scratch'];
            $count_solo1 = (int) $data1['count_solo'];

            $dato = [
                'ISBN' => $libro->ISBN,
                'titulo' => $libro->titulo,
                'type' => $libro->type,
                'editorial' => $libro->editorial,
                'piezas_1' => $libro->piezas,
                'scratch1' => $sum_scratch1,
                'solo1' => $count_solo1,
                'defectuosos_1' => $libro->defectuosos,
                'piezas_2' => 0,
                'scratch2' => 0,
                'solo2' => 0,
                'defectuosos_2' => 0,
                'total_piezas' => $libro->piezas, 
                'total_scratch' => $sum_scratch1, 
                'total_solo' => $count_solo1, 
                'total_defectuosos' => $libro->defectuosos
            ];

            $s2->map(function($opuesto) use ($libro, &$dato){
                if($opuesto->titulo == $libro->titulo){
                    $data2 = $this->get_only_scratch('opuesto', $opuesto->id, $opuesto->piezas, $opuesto->type);
                    $sum_scratch2 = (int) $data2['sum_scratch'];
                    $count_solo2 = (int) $data2['count_solo'];
                    $dato['piezas_2'] = $opuesto->piezas;
                    $dato['scratch2'] = $sum_scratch2;
                    $dato['solo2'] = $count_solo2;
                    $dato['defectuosos_2'] = $opuesto->defectuosos;
                    $dato['total_piezas'] = $dato['total_piezas'] + $opuesto->piezas;
                    $dato['total_scratch'] = $dato['total_scratch'] + $sum_scratch2;
                    $dato['total_solo'] = $dato['total_solo'] + $count_solo2;
                    $dato['total_defectuosos'] = $dato['total_defectuosos'] + $opuesto->defectuosos;
                }
            });

            $ls->push($dato);
        });
        return $ls;
    }

    public function all_libro(Request $request){
        $s1 = Libro::where('titulo', 'like', '%'.$request->titulo.'%')
                        ->where('estado', 'activo')
                        ->where('editorial', 'MAJESTIC EDUCATION')->get();
        $s2 = Libro::on('opuesto')->where('titulo', 'like', '%'.$request->titulo.'%')
                        ->where('estado', 'activo')
                        ->where('editorial', 'MAJESTIC EDUCATION')->get();
        $ls = $this->organizar_todo($s1, $s2);
        return response()->json($ls);
    }

    // DESCARGAR AMBOS INVENTARIOS (MAJESTIC Y OMEGA)
    public function download_both(){
        $hoy 	= Carbon::now();
        return Excel::download(new BothExport, $hoy->format('Y-m-d').'.xlsx');
    }

    public function all_sistemas(){
        return view('information.libros.lista-sistemas');
    }

    // *** SCRATCH
    // SELECT QUE SE OCUPA PARA OBTENER LOS PACKS
    public function get_select_scratch(){
        return \DB::table('packs')
            ->join('libros as lf', 'packs.libro_fisico', '=', 'lf.id')
            ->join('libros as ld', 'packs.libro_digital', '=', 'ld.id')
            ->join('series', 'lf.serie_id', '=', 'series.id')
            ->select([
                'series.serie',
                'lf.titulo as fisico',
                'ld.titulo as digital',
                'packs.piezas'
            ])->where('lf.estado', 'activo')
            ->orderBy('series.serie', 'asc')
            ->orderBy('lf.titulo', 'asc');
    }

    // OBTENER LA LISTA DE TODOS LOS SCRATCH
    public function scratch_all(){
        $packs = $this->get_select_scratch()->paginate(25);
        return response()->json($packs);
    }

    // OBTENER LIBROS EN SCRATCH POR SERIE SELECCIONADA
    public function scratch_by_serie(Request $request){
        $packs = $this->get_select_scratch()->where('series.id', $request->serie_id)->paginate(25);
        return response()->json($packs);
    }

    // OBTENER LIBROS EN SCRATCH POR LIBRO SELECCIONADO (FISICO/DIGITAL)
    public function scratch_by_book(Request $request){
        $packs = $this->get_select_scratch()->where('lf.id', $request->libro_id)->orWhere('ld.id', $request->libro_id)->paginate(25);
        return response()->json($packs);
    }

    // DESCARGAR LISTA DE SCRATCH
    public function download_list_scratch(){
        $hoy = Carbon::now();
        return Excel::download(new ScratchExport, $hoy->format('Y-m-d').'_INVENTARIO-SCRATCH.xlsx');
    }

    // *** REVISAR DE AQUI PARA ABAJO CONSULTAS DE SCRATCH, PORQUE CREO QUE ALGUNAS SE ESTAN REPITIENDO
    // BUSQUEDA DE SCRATCH, EN BASE AL LIBRO FISICO
    public function scratch_by_titulo(Request $request){
        $query = \DB::table('libros')->join('packs', 'libros.id', '=', 'packs.libro_fisico')
                    ->select('libros.titulo as lf_titulo', 'packs.*')
                    ->where('titulo','like','%'.$request->titulo.'%')
                    ->where('estado', 'activo')
                    ->where('type', 'venta')
                    ->orderBy('titulo', 'asc')
                    ->get();
        $libros = collect();
        $query->map(function($q) use (&$libros){
            $ld_titulo = \DB::table('libros')->where('id', $q->libro_digital)->first();
            $libros->push([
                'lf_titulo' => $q->lf_titulo,
                'ld_titulo' => $ld_titulo->titulo,
                'id' => $q->id,
                'libro_fisico' => $q->libro_fisico,
                'libro_digital' => $q->libro_digital,
                'piezas' => $q->piezas
            ]);
        });
        return response()->json($libros);
    }

    // OBTENER LIBRO FISICO Y DIGITAL DE PACK SELECCIONADO
    public function scratch_libros(Request $request){
        $libros = Libro::whereIn('id', [$request->f, $request->d])
                    ->orderBy('type', 'asc')->get();
        return response()->json($libros);
    }

    public function get_scratch(Request $request){
        $packs = Pack::where('libro_fisico', $request->id)
                        ->OrWhere('libro_digital', $request->id)
                        ->sum('piezas');
        return response()->json($packs);
    }

    public function save_pack(Request $request){
        $libro_fisico = $request->libro_fisico;
        $libro_digital = $request->libro_digital;
        $pack = Pack::where('libro_fisico', $libro_fisico)
                        ->where('libro_digital', $libro_digital)->first();
        if(!$pack){
            Pack::create([
                'libro_fisico' => $libro_fisico, 
                'libro_digital' => $libro_digital
            ]);
            return response()->json(true);
        }
        return response()->json(false);
    }

    // SERIES
    // OBTENER CONCIDENCIA DE NOMBRES DE SERIE
    public function get_series(Request $request){
        $series = \DB::table('series')->select('id', 'serie')
            ->where('serie','LIKE','%'.$request->querySerie.'%')
            ->orderBy('serie', 'asc')->get();
        return response()->json($series);
    }

    // CLAVES
    // DESCARGAR LISTA DE CLAVES
    public function download_list_claves($tipo){
        $hoy = Carbon::now();
        return Excel::download(new ClavesExport($tipo), $hoy->format('Y-m-d').'_INVENTARIO-LICENCIAS-DEMOS.xlsx');
    }
}
