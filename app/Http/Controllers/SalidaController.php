<?php

namespace App\Http\Controllers;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\salidas\SalidaExport;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Sregistro;
use App\Reporte;
use App\Salida;

class SalidaController extends Controller
{
    // GUARDAR SALIDA
    public function store(Request $request){
        $this->validate($request, [
            'folio' => 'min:3|max:100|required|string|unique:salidas'
        ]);
        \DB::beginTransaction();
        try {
            $salida = Salida::create([
                'creado_por' => auth()->user()->name,
                'folio' => $request->folio
            ]);
    
            $libros = collect($request->libros);
            $lista = [];
            $hoy = Carbon::now();
            $count = 0;
            $libros->map(function($registro) use(&$lista, $salida, $hoy, &$count){
                $libro_id = $registro['id'];
                $unidades = (int) $registro['unidades'];

                $lista[] = [
                    'salida_id' => $salida->id,
                    'libro_id' => $libro_id, 
                    'unidades' => $unidades,
                    'created_at' => $hoy,
                    'updated_at' => $hoy
                ];

                $count += $unidades;
            });
    
            $salida->update(['unidades' => $count]);
            Sregistro::insert($lista);

            $reporte = 'creo la salida '.$salida->folio;
            $this->create_report($salida->id, $reporte);
            
            \DB::commit();
        } catch (Exception $e) {
            \DB::rollBack();
            return response()->json($exception->getMessage());
        }
        return response()->json(true);
    }

    // OBTENER TODAS LAS SALIDAS
    public function index(){
        $salidas = Salida::orderBy('created_at', 'desc')->paginate(20);
        return response()->json($salidas);
    }

    // OBTENER DETALLES DE LA SALIDA
    public function show(Request $request){
        $salida = Salida::whereId($request->salida_id)
            ->with('sregistros.libro', 'saldevoluciones.libro')->first();
        return response()->json($salida);
    }

    // DESCARGAR EN EXCEL NOTA DE SALIDA
    public function download($id){
        return Excel::download(new SalidaExport($id), 'salida.xlsx');
    }

    public function create_report($salida_id, $reporte){
        Reporte::create([
            'user_id' => auth()->user()->id, 
            'type' => 'proveedor', 
            'reporte' => $reporte,
            'name_table' => 'salidas', 
            'id_table' => $salida_id
        ]);
    }
}
