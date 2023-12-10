<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class GAccountExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function headings(): array
    {
        return [
            // 'id',
            'CLIENTE', 
            'TOTAL',
            'PAGOS',
            'DEVOLUCIÓN',
            'PAGAR'
        ];
    }

    public function collection()
    {
        $remisiones = \DB::table('remclientes')
            ->join('clientes', 'remclientes.cliente_id', '=', 'clientes.id')
            ->select(
                // 'clientes.id as id', 
                'clientes.name as name', 
                'total', 'total_pagos', 
                'total_devolucion', 'total_pagar'
            )
            ->groupBy('clientes.name', 'total', 'total_devolucion', 'total_pagos', 'total_pagar')
            ->orderBy('clientes.name', 'asc')
            ->get();
        return $remisiones;
    }
}
