<?php

namespace App\Exports;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\Devolucione;
use App\Libro;

class MovLibrosExport implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */

    public function __construct($editorial, $type)
    {
        $this->editorial = $editorial;
        $this->type = $type;
    }

    public function view(): View
    {
        $movimientos = $this->movimientos_por_edit($this->editorial);
        

        if($this->type == '1') {
            return view('download.excel.libros.movimientos', [
                'movimientos' => $movimientos
            ]);
        } else {
            return view('download.excel.movimientos.libros-detallado', [
                'movimientos' => $movimientos
            ]);
        }
    }

    // MOVIMIENTOS DE UN LIBRO
    public function movimientos_por_edit($editorial){
        if($editorial === 'TODO'){
            $libros = $this->get_libros();
        } else{
            $libros = $this->get_libros_editorial($editorial);
        }
        
        if($this->type == '1')
            $movimientos = $this->busqueda_unidades($libros);
        else
            $movimientos = $this->assign_detallado($libros);
        return $movimientos;
    }

    public function get_libros(){
        $libros = \DB::table('libros')->orderBy('titulo', 'asc')->get();
        return $libros;
    }

    public function get_libros_editorial($editorial){
        $libros = \DB::table('libros')
                ->where('editorial', $editorial)
                ->select('id', 'editorial', 'ISBN', 'titulo', 'piezas', 'defectuosos')
                ->where('estado', 'activo')
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
                            ->select('libro_id as libro_id', \DB::raw('SUM(unidades) as entradas'))
                            ->groupBy('libro_id')
                            ->get(); 
        $devoluciones = \DB::table('fechas')
                            // ->where('fechas.created_at','like', '%2022-12%')
                            ->join('remisiones', 'fechas.remisione_id', '=', 'remisiones.id')
                            ->whereNotIn('remisiones.corte_id', [4])
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

    public function assign_detallado($libros){
        $movimientos = array();
        foreach($libros as $libro){
            $relacion = [
                'ISBN' => $libro->ISBN,
                'libro' => $libro->titulo,
                'existencia' => $libro->piezas,
                'detalles' => $this->detalles_movimientos($libro->id)
            ];
            array_push($movimientos, $relacion);
        }   
        return $movimientos;
    }

    public function detalles_movimientos($libro_id){
        // ENTRADAS
        $entradas = \DB::table('registros')
                        ->join('entradas', 'registros.entrada_id', 'entradas.id')
                        ->where('libro_id', $libro_id)
                        ->select('entradas.created_at as fecha', 'entradas.folio as folio', 'registros.unidades as unidades')
                        ->orderBy('entradas.created_at', 'desc')
                        ->get();
        $devoluciones = \DB::table('devoluciones')
                            ->join('remisiones', 'devoluciones.remisione_id', '=', 'remisiones.id')
                            ->join('clientes', 'remisiones.cliente_id', '=', 'clientes.id')
                            ->where('libro_id', $libro_id)
                            ->where('unidades', '>', 0)
                            ->select('devoluciones.created_at as fecha', 'clientes.name as cliente', 'remisione_id as folio', 'unidades as unidades')
                            ->orderBy('remisiones.id', 'desc')
                            ->get(); 
        $saldevoluciones = \DB::table('saldevoluciones')
                                ->join('salidas', 'saldevoluciones.salida_id', 'salidas.id')
                                ->where('libro_id', $libro_id)
                                ->select('saldevoluciones.created_at as fecha', 'salidas.folio as folio', 'saldevoluciones.unidades as unidades')
                                ->orderBy('salidas.created_at', 'desc')
                                ->get();
        $prodevoluciones = \DB::table('prodevoluciones')
                                ->join('promotions', 'prodevoluciones.promotion_id', 'promotions.id')
                                ->where('libro_id', $libro_id)
                                ->select('prodevoluciones.created_at as fecha', 'promotions.folio as folio', 'prodevoluciones.unidades as unidades')
                                ->orderBy('promotions.created_at', 'desc')
                                ->get();
        // SALIDAS
        $entdevoluciones = \DB::table('entdevoluciones')
                                ->join('registros', 'entdevoluciones.registro_id', 'registros.id')
                                ->join('entradas', 'entdevoluciones.entrada_id', 'entradas.id')
                                ->where('libro_id', $libro_id)
                                ->select('entdevoluciones.created_at as fecha', 'entradas.folio as folio', 'entdevoluciones.unidades as unidades')
                                ->orderBy('entdevoluciones.created_at', 'desc')
                                ->get();
        $remisiones = \DB::table('datos')
                        ->join('remisiones', 'datos.remisione_id', '=', 'remisiones.id')
                        ->join('clientes', 'remisiones.cliente_id', '=', 'clientes.id')
                        ->where('libro_id', $libro_id)
                        ->whereNotIn('remisiones.estado', ['Cancelado'])
                        ->whereNull('datos.deleted_at')
                        ->select('remisiones.created_at as fecha', 'clientes.name as cliente', 'remisiones.id as folio', 'datos.unidades as unidades')
                        ->orderBy('remisiones.id', 'desc')
                        ->get();
        $notas = \DB::table('registers')
                        ->join('notes', 'registers.note_id', 'notes.id')
                        ->where('libro_id', $libro_id)
                        ->select('notes.created_at as fecha', 'notes.folio as folio', 'registers.unidades as unidades')
                        ->orderBy('notes.created_at', 'desc')
                        ->get();
        $promociones = \DB::table('departures')
                            ->join('promotions', 'departures.promotion_id', 'promotions.id')
                            ->where('libro_id', $libro_id)
                            ->select('promotions.created_at as fecha', 'promotions.folio as folio', 'departures.unidades as unidades')
                            ->orderBy('promotions.created_at', 'desc')
                            ->get();
        $donaciones = \DB::table('donaciones')
                        ->join('regalos', 'donaciones.regalo_id', 'regalos.id')
                        ->where('libro_id', $libro_id)
                        ->select('regalos.created_at as fecha', 'regalos.plantel as folio', 'donaciones.unidades as unidades')
                        ->orderBy('regalos.created_at', 'desc')
                        ->get();
        $salidas = \DB::table('sregistros')
                        ->join('salidas', 'sregistros.salida_id', '=', 'salidas.id')
                        ->where('salidas.estado', 'enviado')
                        ->where('libro_id', $libro_id)
                        ->select('salidas.created_at as fecha', 'salidas.folio as folio', 'sregistros.unidades as unidades')
                        ->orderBy('salidas.created_at', 'desc')
                        ->get();


        return [
            'entradas' => $entradas,
            'devoluciones' => $devoluciones,
            'saldevoluciones' => $saldevoluciones,
            'prodevoluciones' => $prodevoluciones,
            'salidas' => $salidas,
            'entdevoluciones' => $entdevoluciones,
            'remisiones' => $remisiones,
            'notas' => $notas,
            'donaciones' => $donaciones,
            'promociones' => $promociones
        ];
    }
}
