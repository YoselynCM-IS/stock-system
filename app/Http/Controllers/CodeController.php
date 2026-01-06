<?php

namespace App\Http\Controllers;

use App\Exports\codes\ByRemisionExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\libros\CodesImport;
use Illuminate\Http\Request;
use App\Remisione;
use App\Libro;
use App\Clave;
use App\Code;
use App\Dato;
use App\Pack;

class CodeController extends Controller
{
    // OBTENER LA VISTA PRINCIPAL DE CODIGOS
    public function codes(){
        return view('information.libros.codes');
    }

    // LISTA DE CODIGOS
    public function index(){
        $codes = Code::whereNotIn('estado', ['proceso', 'eliminado'])
                        ->orderBy('created_at', 'desc')
                        ->with('libro')->paginate(50);
        return response()->json($codes);
    }

    // OBTENER CODIGOS POR TITULO
    public function by_libro(Request $request){
        $codes = Code::where('libro_id', $request->libro_id)
                        ->whereNotIn('estado', ['proceso', 'eliminado'])
                        ->orderBy('created_at', 'desc')
                        ->with('libro')->paginate(50);
        return response()->json($codes);
    }

    // OBTENER CODIGOS POR EDITORIAL
    public function by_editorial(Request $request){
        $libros = Libro::where('type','digital')
                    ->where('editorial', $request->editorial)
                    ->where('estado', 'activo')
                    ->select('id')
                    ->orderBy('titulo', 'asc')->get();
        $ids = [];
        $libros->map(function($libro) use(&$ids){
            $ids[] = $libro->id;
        });
        $codes = Code::whereIn('libro_id', $ids)
                        ->whereNotIn('estado', ['proceso', 'eliminado'])
                        ->orderBy('created_at', 'desc')
                        ->with('libro')->paginate(50);
        return response()->json($codes);
    }

    // OBTENER CODIGOS POR EL CLIENTE AL QUE SE LE VENDIO
    public function show_remisiones(Request $request){
        $datos = \DB::table('datos')
                    ->join('remisiones', 'datos.remisione_id', '=', 'remisiones.id')
                    ->join('libros', 'datos.libro_id', '=', 'libros.id')
                    ->join('code_dato', 'datos.id', '=', 'code_dato.dato_id')
                    ->where('code_dato.code_id', $request->code_id)
                    // ->where('remisiones.cliente_id', $request->cliente_id)
                    ->whereNotIn('remisiones.estado', ['Cancelado'])
                    ->where('libros.type', 'digital')
                    ->select('remisiones.id as remisione_id')
                    // ->select('code_dato.code_id as code_id')
                    ->orderBy('remisiones.id', 'asc')
                    ->get();

        $remisiones_ids = [];
        $datos->map(function($dato) use(&$remisiones_ids){
            $remisiones_ids[] = $dato->remisione_id;
        });

        $remisiones = Remisione::whereIn('id', $remisiones_ids)
                                ->with('cliente')->get();
        return response()->json($remisiones);
    }

    // BUSCAR POR CODIGO
    public function by_code(Request $request){
        $codes = Code::where('codigo', 'like', '%'.$request->code.'%')
                        ->whereNotIn('estado', ['proceso', 'eliminado'])
                        ->orderBy('created_at', 'desc')
                        ->with('libro')->paginate(50);
        return response()->json($codes);
    }

    // SUBIR CODIGOS
    public function upload(Request $request){
        $array = Excel::toArray(new CodesImport, request()->file('file'));
        try {
            $count = 0;
            $libro = Libro::find($request->libro_id);
            $tipo = $request->tipo;
            $lista = collect($array[0]);
            $codigos = collect();

            $lista->map(function($row) use(&$codigos, &$count, $libro, $tipo){
                if($row[0] == $libro->titulo){
                    if($row[2] == $tipo){
                        $code = Code::where('codigo', $row[1])->firstOr(function () use($libro, $row, &$codigos, &$count) {
                            $code = Code::create([
                                'libro_id' => $libro->id, 
                                'codigo' => $row[1],
                                'tipo'  => $row[2]
                            ]);
                            $codigos->push($code);
                            $count++;
                            return $code;
                        });
                    }
                }                
            });

            // $libro->update(['piezas' => $libro->piezas + $count]);
            $datos = ['codes' => $codigos, 'libro_id' => $libro->id, 'libro' => $libro->titulo, 'tipo' => $tipo,'unidades' => $count];
        }  catch (Exception $e) {
            $success = $exception->getMessage();
        }
        
        return response()->json($datos);
    }

    // DESCARGAR CODIGOS POR REMISION
    public function download_byremision($remisione_id){
        return Excel::download(new ByRemisionExport($remisione_id), 'rem-'.$remisione_id.'-codigos.xlsx');
    }

    public function check_byclave(Request $request){
        $clave = $this->checkifexist_claves($request);
        return response()->json($clave);
    }

    public function licencias_demos(){
        return view('information.codes.lista'); 
    }

    // *** AQUI EMPIEZAN FUNCIONES DE CLAVES (LICENCIAS/DEMOS)
    // REVISAR SI EXISTE LA CLAVE
    public function checkifexist_claves($request){
        return \DB::table('claves')->where('libro_id', $request->libro_id)->where('tipo', $request->tipo)->first();
    }

    // GUARDAR CLAVE NUEVA
    public function claves_store(Request $request){
        $clave = $this->checkifexist_claves($request);
        if(!$clave){
            Clave::create([ 'libro_id' => $request->libro_id,  'tipo' => $request->tipo ]);
            return response()->json(true);
        }
        return response()->json(false);
    }

    // FUNCION GENERAL PARA OBTENER LAS CLAVES
    public function get_select_claves(){
        return \DB::table('claves')
            ->select('claves.*', 'libros.titulo')
            ->join('libros', 'claves.libro_id', '=', 'libros.id')
            ->join('series', 'libros.serie_id', '=', 'series.id')
            ->orderBy('series.serie', 'asc')->orderBy('libros.titulo', 'asc')->orderBy('claves.tipo', 'asc');
    }

    // OBTENER TODAS LAS CLAVES
    public function claves_all(){
        $claves = $this->get_select_claves()->paginate(25);
        return response()->json($claves);
    }

    // OBTENER CLAVES POR LIBRO SELECCIONADO
    public function claves_by_book(Request $request){
        $claves = $this->get_select_claves()->where('claves.libro_id', $request->libro_id)->paginate(25);
        return response()->json($claves);
    }

    // OBTENER CLAVES POR TIPO SELECCIONADO
    public function claves_by_tipo(Request $request){
        $claves = $this->get_select_claves()->where('claves.tipo', $request->tipo)->paginate(25);
        return response()->json($claves);
    }
    // *** AQUI TERMINAN FUNCIONES DE CLAVES (LICENCIAS/DEMOS)

    public function scratch(){
        return view('information.codes.scratch'); 
    }

    // *** YA NO SE UTILIZA POR AHORA, PERO PUEDE SERVIR PARA LA COMPROBACIÓN DE INVENTARIO CON DISPONIBLE DE CODIGOS
    public function group_count_codes($tipo) {
        return Code::select('libro_id', \DB::raw('COUNT(id) as inventario'))
                    ->with('libro')
                    ->where('estado', 'inventario')
                    ->where('tipo', $tipo)
                    ->groupBy('libro_id')
                    ->get();
    }
}
