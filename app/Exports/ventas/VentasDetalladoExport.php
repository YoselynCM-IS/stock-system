<?php

namespace App\Exports\ventas;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class VentasDetalladoExport implements FromCollection, WithHeadings
{
    public function headings(): array
    {
        return [
            'EDITORIAL',
            'TIPO (LIBRO)',
            'ISBN',
            'TITULO',
            'COSTO UNITARIO',
            'UNIDADES',
            'TOTAL',
            'REMISIÓN',
            'TIPO (CLIENTE)',
            'CLIENTE',
            'CREADO EL'
        ];
    }

    protected $fecha_inicio;
    protected $fecha_final;
    
    public function __construct($fecha_inicio, $fecha_final)
    {
        $this->fecha_inicio = $fecha_inicio;
        $this->fecha_final = $fecha_final;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $datos = $this->get_datos_ventas()
                ->whereBetween('remisiones.fecha_creacion', [
                    $this->fecha_inicio, $this->fecha_final]);
        return $this->select_datos_ventas($datos);
    }

    public function get_datos_ventas(){
        return \DB::table('remisiones')
                ->join('clientes', 'remisiones.cliente_id', '=', 'clientes.id')
                ->join('datos', 'remisiones.id', '=', 'datos.remisione_id')
                ->join('libros', 'datos.libro_id', '=', 'libros.id')
                ->where('remisiones.estado', '!=', 'Cancelado')
                ->where('remisiones.deleted_at', NULL)
                ->where('datos.deleted_at', NULL);
    }

    public function select_datos_ventas($datos){
        return $datos->select(
            'libros.editorial as libro_editorial', 
            'libros.type as libro_type', 
            'libros.ISBN as libro_isbn', 
            'libros.titulo as libro_titulo', 
            'datos.costo_unitario as dato_costo', 
            'datos.unidades as dato_unidades', 
            'datos.total as dato_total',
            'remisiones.id as remisione_id', 
            'clientes.tipo as cliente_tipo', 
            'clientes.name as cliente_name', 
            'remisiones.fecha_creacion as rem_fecha'
        )->orderBy('libro_editorial', 'asc')
        ->orderBy('libro_titulo', 'asc')->get();
    }
}
