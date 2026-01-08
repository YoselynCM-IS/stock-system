<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Remisione;
use Luecano\NumeroALetras\NumeroALetras;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class RemisionExport implements FromView, WithColumnFormatting
{
    /**
    * @return \Illuminate\Support\Collection
    */

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function columnFormats(): array {
        return [
            'B' => NumberFormat::FORMAT_NUMBER 
        ];
    }

    public function view(): View
    {
        $remision = $this->get_remision();
        $fecha = new Carbon($remision->fecha_entrega);
        $datos = $this->get_datos();
        $formatter = new NumeroALetras();
        $maximo = 37 - ($datos->count() + 18);
        if($maximo <= 0) $maximo = 1;

        return view('download.excel.remisiones.remision', [
            'fecha' => $fecha,
            'remision' => $remision,
            'datos' => $datos,
            'total_letras' => $formatter->toWords($remision->total, 2),
            'maximo' => $maximo
        ]);
    }

    public function get_remision(){
        return Remisione::whereId($this->id)->with('cliente')->first();
    }

    public function get_datos(){
        $remdatos = \DB::table('datos')->join('libros', 'datos.libro_id', '=', 'libros.id')
                        ->select('datos.*', 'libros.ISBN', 'libros.titulo', 'libros.type')
                        ->where('datos.remisione_id', $this->id)
                        ->where(function ($query) {
                            return $query->whereNull('datos.pack_id')
                                        ->orWhere(['datos.pack_id' => null, 'libros.type' => 'venta']);
                        })->get();

        $datos = collect();
        $remdatos->map(function($dato) use(&$datos){
            if($dato->pack_id != null) {
                $digital = \DB::table('datos')->join('libros', 'datos.libro_id', '=', 'libros.id')
                            ->where('datos.remisione_id', $dato->remisione_id)
                            ->where('libros.type', 'digital')
                            ->where('pack_id', $dato->pack_id)
                            ->where('datos.unidades', $dato->unidades)
                            ->whereNull('deleted_at')->first();
                $titulo = $dato->titulo.' PACK';
                $costo_unitario = $dato->costo_unitario + $digital->costo_unitario;
                $total = $dato->total + $digital->total;
                
            } else {
                $titulo = $dato->titulo;
                $costo_unitario = $dato->costo_unitario;
                $total = $dato->total;
            }
            $datos->push($this->assign_datos($dato->id, $dato->pack_id, $dato->ISBN, $titulo, $dato->unidades, $costo_unitario, $total));
        });

        return $datos;
    }

    // ASIGNAR DATOS PARA DESCARGAR DATOS
    public function assign_datos($id, $pack_id, $ISBN, $titulo, $unidades, $costo_unitario, $total){
        return [
            'id' => $id,
            'pack_id' => $pack_id,
            'ISBN' => $ISBN,
            'titulo' => $titulo,
            'unidades' => $unidades, 
            'costo_unitario' => $costo_unitario,
            'total' => $total
        ];
    }
}
