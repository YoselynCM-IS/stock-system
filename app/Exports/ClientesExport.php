<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ClientesExport implements FromCollection,WithHeadings
{

    public function headings(): array
    {
        return [
            'CLIENTE', 
            'CONTACTO',
            'DIRECCIÓN',
            'RFC',
            'DIRECCIÓN FISCAL',
            'CORREO',
            'TELÉFONO',
            'CONDICIONES DE PAGO'
        ];
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $clientes = \DB::table('clientes')
                    ->select('name', 'contacto', 'direccion', 'rfc', 'fiscal', 'email', 'telefono', 'condiciones_pago')
                    ->orderBy('name','asc')->get();
        return $clientes;
    }
}
