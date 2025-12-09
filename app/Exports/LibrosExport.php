<?php

namespace App\Exports;

use DB;
use App\Libro;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class LibrosExport implements FromCollection, WithHeadings, WithColumnFormatting{
    /**
    * @return \Illuminate\Support\Collection
    */

    protected $editorial;
    
    public function __construct($editorial, $serie_id, $titulo, $isbn, $type)
    {
        $this->editorial = $editorial;
        $this->serie_id = $serie_id;
        $this->titulo = $titulo;
        $this->isbn = $isbn;
        $this->type = $type;
    }

    public function headings(): array
    {
        return [
            'Editorial',
            'Serie',
            'ISBN', 
            'Libro',
            'Tipo',
            'Piezas',
            'Defectuosos'
        ];
    }

    public function columnFormats(): array {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_NUMBER,
            'D' => NumberFormat::FORMAT_TEXT,
            'E' => NumberFormat::FORMAT_TEXT, 
            'F' => NumberFormat::FORMAT_NUMBER, 
            'G' => NumberFormat::FORMAT_NUMBER, 
        ];
    }

    public function collection(){ 
        $libros = \DB::table('libros')->join('series', 'series.id', '=', 'libros.serie_id')
            ->select('libros.editorial', 'series.serie', 'libros.ISBN', 'libros.titulo', 'libros.type', 'libros.piezas', 'libros.defectuosos')
            ->orderBy('libros.editorial', 'asc')
            ->orderBy('series.serie', 'asc')
            ->orderBy('libros.type', 'desc')
            ->orderBy('libros.titulo', 'asc')
            ->where('libros.estado', 'activo')
            ->when($this->editorial != 'null', function ($query) {
                $query->where('editorial', $this->editorial);
            })
            ->when($this->serie_id != 'null', function ($query) {
                $query->where('serie_id', $this->serie_id);
            })
            ->when($this->titulo != 'null', function ($query) {
                $query->where('titulo','like','%'.$this->titulo.'%');
            })
            ->when($this->isbn != 'null', function ($query) {
                $query->where('ISBN','like','%'.$this->isbn.'%');
            })
            ->when($this->type != 'null', function ($query) {
                $query->where('type', $this->type);
            })
            ->get();
        return $libros;
    }
}
