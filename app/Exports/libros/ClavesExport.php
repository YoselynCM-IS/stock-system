<?php

namespace App\Exports\libros;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ClavesExport implements FromCollection, WithHeadings, WithColumnFormatting
{
    protected $tipo;
    
    public function __construct($tipo)
    {
        $this->tipo = $tipo;
    }

    public function headings(): array
    {
        return [
            'Libro',
            'Tipo', 
            'Piezas'
        ];
    }

    public function columnFormats(): array {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_NUMBER, 
        ];
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return \DB::table('claves')->select('libros.titulo', 'claves.tipo', 'claves.piezas')
            ->join('libros', 'claves.libro_id', '=', 'libros.id')
            ->join('series', 'libros.serie_id', '=', 'series.id')
            ->when($this->tipo != 'null', function ($query) {
                $query->where('claves.tipo', $this->tipo);
            })->orderBy('series.serie', 'asc')->orderBy('libros.titulo', 'asc')->orderBy('claves.tipo', 'asc')->get();
    }
}
