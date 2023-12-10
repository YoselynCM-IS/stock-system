<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Remcliente;
use App\Remdeposito; 
use App\Remisione;
use App\Deposito;
use Carbon\Carbon;

class ContadorController extends Controller
{
    public function remisiones() {
        return view('contador.remisiones');
    }

    public function pagos(){
        return view('contador.pagos');
    }

    public function obtenerPagos(){
        $today = Carbon::now()->format('Y-m');
        $pagos = $this->buscarPagos($today);
        return response()->json($pagos);
    }

    public function pagosFecha(Request $request){
        $mes = $request->mes;
        if($mes != 'TODO'){
            $year = Carbon::now()->format('Y');
            $today = $year.'-'.$mes;
            $pagos = $this->buscarPagos($today);
        } else { $pagos = $this->obtenerTodo(); }
        return response()->json($pagos);
    }

    public function buscarPagos($today){
        $remdepositos = \DB::table('remdepositos')
                    ->join('remclientes', 'remdepositos.remcliente_id', '=', 'remclientes.id')
                    ->join('clientes', 'remclientes.cliente_id', '=', 'clientes.id')
                    ->where('remdepositos.created_at', 'like', '%'.$today.'%')
                    ->select('remdepositos.created_at as created_at', 'clientes.name as cliente', 'pago')
                    ->orderBy('remdepositos.created_at', 'desc')->get();
        $depositos = \DB::table('depositos')
                    ->join('remisiones', 'depositos.remisione_id', '=', 'remisiones.id')
                    ->join('clientes', 'remisiones.cliente_id', '=', 'clientes.id')
                    ->where('depositos.created_at', 'like', '%'.$today.'%')
                    ->select('depositos.created_at as created_at', 'clientes.name as cliente', 'pago')
                    ->orderBy('depositos.created_at', 'desc')
                    ->get();

        $unir = array();
        foreach($remdepositos as $remdeposito){ array_push($unir, $remdeposito); }
        foreach($depositos as $deposito){ array_push($unir, $deposito); }

        $pagos = $this->convertirArray($unir);
        return $pagos;
    }

    public function obtenerTodo(){
        $remdepositos = \DB::table('remdepositos')
                    ->join('remclientes', 'remdepositos.remcliente_id', '=', 'remclientes.id')
                    ->join('clientes', 'remclientes.cliente_id', '=', 'clientes.id')
                    ->select('remdepositos.created_at as created_at', 'clientes.name as cliente', 'pago')
                    ->orderBy('remdepositos.created_at', 'desc')->get();
        $depositos = \DB::table('depositos')
                    ->join('remisiones', 'depositos.remisione_id', '=', 'remisiones.id')
                    ->join('clientes', 'remisiones.cliente_id', '=', 'clientes.id')
                    ->select('depositos.created_at as created_at', 'clientes.name as cliente', 'pago')
                    ->orderBy('depositos.created_at', 'desc')
                    ->get();

        $unir = array();
        foreach($remdepositos as $remdeposito){ array_push($unir, $remdeposito); }
        foreach($depositos as $deposito){ array_push($unir, $deposito); }

        $pagos = $this->convertirArray($unir);
        return $pagos;
    }

    public function convertirArray($unir){
        $array = collect($unir)->sortBy('created_at')->reverse()->toArray();

        $pagos = array();
        foreach($array as $clave => $valor){
            array_push($pagos, $valor);
        }
        return $pagos;
    }

    public function movimientos_monto(){
        $editoriales = \DB::table('editoriales')->orderBy('editorial', 'asc')->get();
        return view('contador.movimientos', compact('editoriales'));
    }

    // public function all_payments(){
    //     $cliente_id = Input::get('cliente_id');
    //     $remdepositos = \DB::table('remdepositos')
    //                     ->join('remclientes', 'remdepositos.remcliente_id', '=', 'remclientes.id')
    //                     ->join('clientes', 'remclientes.cliente_id', '=', 'clientes.id')
    //                     ->select('remdepositos.created_at', 'clientes.name as cliente', 'pago')
    //                     ->where('remclientes.cliente_id', $cliente_id)
    //                     ->orderBy('remdepositos.created_at', 'desc')->get();
    //     $depositos = \DB::table('depositos')
    //                     ->join('remisiones', 'depositos.remisione_id', '=', 'remisiones.id')
    //                     ->join('clientes', 'remisiones.cliente_id', '=', 'clientes.id')
    //                     ->select('depositos.created_at', 'clientes.name as cliente', 'pago')
    //                     ->where('remisiones.cliente_id', $cliente_id)
    //                     ->orderBy('depositos.created_at', 'desc')->get();
    //     return response()->json(['remdepositos' => $remdepositos, 'depositos' => $depositos]);
    // }

    // public function date_payments(){
    //     $inicio = Input::get('inicio');
    //     $final = Input::get('final');
    //     $remdepositos = \DB::table('remdepositos')
    //                     ->join('remclientes', 'remdepositos.remcliente_id', '=', 'remclientes.id')
    //                     ->join('clientes', 'remclientes.cliente_id', '=', 'clientes.id')
    //                     ->select('remdepositos.created_at', 'clientes.name as cliente', 'pago')
    //                     ->whereBetween('remdepositos.created_at', [$inicio, $final])
    //                     ->orderBy('remdepositos.created_at', 'desc')->get();
    //     $depositos = \DB::table('depositos')
    //                     ->join('remisiones', 'depositos.remisione_id', '=', 'remisiones.id')
    //                     ->join('clientes', 'remisiones.cliente_id', '=', 'clientes.id')
    //                     ->select('depositos.created_at', 'clientes.name as cliente', 'pago')
    //                     ->whereBetween('depositos.created_at', [$inicio, $final])
    //                     ->orderBy('depositos.created_at', 'desc')->get();
    //     return response()->json(['remdepositos' => $remdepositos, 'depositos' => $depositos]);
    // }
}
