<?php

namespace App\Exports\libros;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ScratchExport implements FromCollection, WithHeadings, WithColumnFormatting
{
    public function headings(): array
    {
        return [
            'Serie',
            'Libro físico', 
            'Libro digital',
            'Piezas'
        ];
    }

    public function columnFormats(): array {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_TEXT,
            'D' => NumberFormat::FORMAT_NUMBER, 
        ];
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return \DB::table('packs')
            ->join('libros as lf', 'packs.libro_fisico', '=', 'lf.id')
            ->join('libros as ld', 'packs.libro_digital', '=', 'ld.id')
            ->join('series', 'lf.serie_id', '=', 'series.id')
            ->select([
                'series.serie',
                'lf.titulo as fisico',
                'ld.titulo as digital',
                'packs.piezas'
            ])->where('lf.estado', 'activo')
            ->orderBy('series.serie', 'asc')
            ->orderBy('lf.titulo', 'asc')->get();
    }
}
