<?php

namespace App\Http\Controllers;

use App\Exports\codes\ByRemisionExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\libros\CodesImport;
use Illuminate\Http\Request;
use App\Remisione;
use App\Libro;
use App\Code;
use App\Dato;
use App\Pack;

class CodeController extends Controller
{
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

    public function by_libro_count(Request $request){
        $count = Code::where('libro_id', $request->libro_id)
                    ->where('estado', $request->estado)
                    ->where('tipo', $request->tipo)->count();
        return response()->json($count);
    }

    public function licencias_demos(){
        $profesor = $this->group_count_codes('profesor');
        $demo = $this->group_count_codes('demo');
        return view('information.codes.lista', compact('profesor', 'demo'));
    }

    public function scratch(){
        $scratch = collect();
        $packs = \DB::table('packs')->join('libros', 'packs.libro_fisico', '=', 'libros.id')
                    ->select('libros.titulo as titulo', 'packs.*')
                    ->orderBy('libros.titulo', 'asc')
                    ->get();
        $packs->map(function($pack) use(&$scratch){
            $libro_digital = Libro::find($pack->libro_digital);
            $scratch->push([
                'titulo' => 'PACK: '.$pack->titulo,
                'fisico' => $pack->titulo,
                'digital' => $libro_digital->titulo,
                'piezas' => $pack->piezas
            ]);
        }); 
        return view('information.codes.scratch', compact('scratch'));
    }

    public function group_count_codes($tipo) {
        return Code::select('libro_id', \DB::raw('COUNT(id) as inventario'))
                    ->with('libro')
                    ->where('estado', 'inventario')
                    ->where('tipo', $tipo)
                    ->groupBy('libro_id')
                    ->get();
    }
}
