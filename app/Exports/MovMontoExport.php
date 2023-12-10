<?php

namespace App\Exports;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class MovMontoExport implements FromView
{
    public function __construct($editorial, $mes)
    {
        $this->editorial = $editorial;
        $this->mes = $mes;
    }

    public function view(): View
    {
        if($this->mes !== 'TODO'){
            $año = Carbon::now()->format('Y');
            $fecha = $año.'-'.$this->mes;
        } else { $fecha = null; }

        $movimientos = $this->all_movmonto();
        return view('download.excel.libros.movimientos_monto', [
            'editorial' => $this->editorial,
            'fecha' => $fecha,
            'movimientos' => $movimientos
        ]);
    }

    public function all_movmonto(){
        if($this->editorial === 'TODO'){
            $libros = $this->get_libros();
        } else{
            $libros = $this->get_libros_editorial($this->editorial);
        }

        if($this->mes === 'TODO'){
            $movimientos = $this->busqueda_monto($libros);
        } else {
            $año = Carbon::now()->format('Y');
            $fecha = $año.'-'.$this->mes;
            $movimientos = $this->busqueda_fecha_monto($libros, $fecha);
        }
        $registros = $this->assign_mov($movimientos);
        // $registros = $movimientos;
        return $registros;
    }

    public function get_libros(){
        $libros = \DB::table('libros')->select('id', 'titulo', 'piezas')->orderBy('titulo', 'asc')->get();
        return $libros;
    }

    public function get_libros_editorial($editorial){
        $libros = \DB::table('libros')
                ->where('editorial', $editorial)
                ->select('id', 'titulo', 'piezas')
                ->orderBy('titulo', 'asc')->get();
        return $libros;
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

    public function assign_mov($movimientos){
        $registros = array();
        foreach($movimientos as $m){
            if($m['total_entrada'] > 0 || $m['total_salida'] > 0)
                array_push($registros, $m);
        }
        return $registros;
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
}
