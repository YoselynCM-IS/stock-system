<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ULibrosExport implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
        $registros = $this->funGral();
        return view('download.excel.gralunidades.ulibros', [
            'registros' => $registros
        ]);
    }

    public function funGral(){
        $unidades = $this->unidades();
        $all = array();
        foreach($unidades as $unidad){
            $new = [
                'gral' => $unidad,
                'clientes' => $this->clientes($unidad['libro_id'])
            ];
            array_push($all, $new);
        }
        return $all;
    }

    public function unidades(){
        $libros = \DB::table('libros')->orderBy('id', 'asc')->get();
        $datos = \DB::table('datos')
                    ->join('remisiones', 'datos.remisione_id', '=', 'remisiones.id')
                    ->whereNotIn('remisiones.estado', ['Cancelado'])
                    ->whereNull('datos.deleted_at')
                    ->select('libro_id as libro_id' ,\DB::raw('SUM(unidades) as unidades'))
                    ->groupBy('libro_id')
                    ->orderBy('libro_id', 'asc')
                    ->get();
        $devoluciones = \DB::table('devoluciones')
                    ->whereNotIn('devoluciones.unidades', [0])
                    ->select('libro_id as libro_id' ,\DB::raw('SUM(unidades) as unidades'))
                    ->groupBy('libro_id')
                    ->orderBy('libro_id', 'asc')
                    ->get();
        
        $registers = array();
        foreach($libros as $libro){
            $registro = [
                'libro_id' => $libro->id,
                'libro' => $libro->titulo,
                'unidades_vendidas' => 0,
                'unidades_remisiones' => 0,
                'unidades_devoluciones' => 0
            ];
            
            foreach($datos as $dato){
                if($dato->libro_id == $libro->id) 
                    $registro['unidades_remisiones'] = $dato->unidades;
            }
            foreach($devoluciones as $devolucione){
                if($devolucione->libro_id == $libro->id) 
                    $registro['unidades_devoluciones'] = $devolucione->unidades;
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

    public function clientes($libro_id){
        $datos = \DB::table('datos')
                    ->join('remisiones', 'datos.remisione_id', '=', 'remisiones.id')
                    ->join('clientes', 'remisiones.cliente_id', '=', 'clientes.id')
                    ->where('libro_id', $libro_id)
                    ->whereNotIn('remisiones.estado', ['Cancelado'])
                    ->whereNull('datos.deleted_at')
                    ->select('clientes.id as cliente_id', 'clientes.name as cliente', \DB::raw('SUM(unidades) as unidades'))
                    ->groupBy('clientes.id', 'clientes.name')
                    ->get();
        $devoluciones = \DB::table('devoluciones')
                    ->join('remisiones', 'devoluciones.remisione_id', '=', 'remisiones.id')
                    ->where('libro_id', $libro_id)
                    ->whereNotIn('unidades', [0])
                    ->select('remisiones.cliente_id as cliente_id', \DB::raw('SUM(unidades) as unidades'))
                    ->groupBy('remisiones.cliente_id')
                    ->get();    
        
        $vendidos = array();
        foreach($datos as $dato){
            $registro = [ 
                'cliente' => $dato->cliente, 
                'unidades_vendidas' => $dato->unidades,
                'unidades_remisiones' => $dato->unidades,
                'unidades_devoluciones' => 0
            ];
            foreach($devoluciones as $devolucione){
                if($dato->cliente_id == $devolucione->cliente_id){
                    $registro['unidades_devoluciones'] = $devolucione->unidades;
                    $registro['unidades_vendidas'] = $dato->unidades - $devolucione->unidades;
                }
            }
            array_push($vendidos, $registro);
        }  
        return $vendidos;      
    }
}
