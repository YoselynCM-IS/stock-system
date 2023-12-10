<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class UnidadesExport implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */

    public function view(): View
    {
        $registros = $this->funGral();
        return view('download.excel.gralunidades.unidades', [
            'registros' => $registros
        ]);
    }

    public function funGral(){
        $unidades = $this->unidades();
        $all = array();
        foreach($unidades as $unidad){
            $new = [
                'gral' => $unidad,
                'libros' => $this->libros($unidad['cliente_id'])
            ];
            array_push($all, $new);
        }
        return $all;
    }
    
    public function unidades(){
        $clientes = \DB::table('clientes')->orderBy('name', 'asc')->get();
        $datos = \DB::table('datos')
                ->join('remisiones', 'datos.remisione_id', '=', 'remisiones.id')
                ->whereNotIn('remisiones.estado', ['Cancelado'])
                ->whereNull('datos.deleted_at')
                ->select(
                    'remisiones.cliente_id as cliente_id',
                    \DB::raw('SUM(datos.unidades) as unidades_remisiones')
                )->groupBy('remisiones.cliente_id')
                ->orderBy('remisiones.cliente_id', 'asc')
                ->get();
        $devoluciones = \DB::table('devoluciones')
                ->join('remisiones', 'devoluciones.remisione_id', '=', 'remisiones.id')
                ->whereNotIn('remisiones.estado', ['Cancelado'])
                ->whereNotIn('devoluciones.unidades', [0])
                ->select(
                    'remisiones.cliente_id as cliente_id',
                    \DB::raw('SUM(devoluciones.unidades) as unidades_devoluciones')
                )->groupBy('remisiones.cliente_id')
                ->orderBy('remisiones.cliente_id', 'asc')
                ->get();

        $registers = array();
        foreach($clientes as $cliente){
            $registro = [
                'cliente_id' => $cliente->id,
                'cliente' => $cliente->name,
                'unidades_remisiones' => 0,
                'unidades_devoluciones' => 0,
                'unidades_vendidas' => 0
            ];
            
            foreach($datos as $dato){
                if($dato->cliente_id == $cliente->id) 
                    $registro['unidades_remisiones'] = $dato->unidades_remisiones;
            }
            foreach($devoluciones as $devolucione){
                if($devolucione->cliente_id == $cliente->id) 
                    $registro['unidades_devoluciones'] = $devolucione->unidades_devoluciones;
            }
            $registro['unidades_vendidas'] = $registro['unidades_remisiones'] - $registro['unidades_devoluciones'];
            array_push($registers, $registro);
        }

        $registros = array();
        foreach($registers as $register){
            if($register['unidades_remisiones'] > 0)
                array_push($registros, $register);
        }

        return $registros;
    }

    public function libros($cliente_id){
        $datos = \DB::table('libros')
                ->join('datos', 'libros.id', '=', 'datos.libro_id')
                ->join('remisiones', 'datos.remisione_id', '=', 'remisiones.id')
                ->whereNotIn('remisiones.estado', ['Cancelado'])
                ->whereNull('datos.deleted_at')
                ->where('remisiones.cliente_id', $cliente_id)
                ->select('libros.titulo as libro', \DB::raw('SUM(datos.unidades) as unidades_remisiones'))
                ->groupBy('libros.titulo')
                ->get();
        $devoluciones = \DB::table('libros')
                ->join('devoluciones', 'libros.id', '=', 'devoluciones.libro_id')
                ->join('remisiones', 'devoluciones.remisione_id', '=', 'remisiones.id')
                ->whereNotIn('remisiones.estado', ['Cancelado'])
                ->whereNotIn('devoluciones.unidades', [0])
                ->where('remisiones.cliente_id', $cliente_id)
                ->select('libros.titulo as libro', \DB::raw('SUM(devoluciones.unidades) as unidades_devoluciones'))
                ->groupBy('libros.titulo')
                ->get();
        $vendidos = array();
        foreach($datos as $dato){
            $registro = [ 
                'libro' => $dato->libro, 
                'unidades_remisiones' => $dato->unidades_remisiones,
                'unidades_devoluciones' => 0,
                'unidades_vendidas' => $dato->unidades_remisiones
            ];
            foreach($devoluciones as $devolucione){
                if($dato->libro == $devolucione->libro){
                    $registro['unidades_devoluciones'] = $devolucione->unidades_devoluciones;
                    $registro['unidades_vendidas'] = $dato->unidades_remisiones - $devolucione->unidades_devoluciones;
                }
            }
            array_push($vendidos, $registro);
        }
        return $vendidos;
    }
}
