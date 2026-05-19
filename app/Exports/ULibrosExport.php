<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;

class ULibrosExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $query;

    // El constructor recibe la consulta construida en el controlador
    public function __construct($query)
    {
        $this->query = $query;
    }

    public function query()
    {
        return $this->query;
    }

    // Definimos los encabezados de las columnas en el Excel
    public function headings(): array
    {
        return [
            'ISBN',
            'Libro',
            'Unidades (Vendidas)',
            'Unidades (Salida)',
            'Unidades (Devoluciones)'
        ];
    }

    // Mapeamos los datos para asegurar que se descarguen con el formato correcto
    public function map($registro): array
    {
        return [
            $registro->isbn,
            $registro->libro,
            (int)$registro->unidades_vendidas,
            (int)$registro->unidades_remisiones,
            (int)$registro->unidades_devoluciones
        ];
    }
}
