<?php

namespace App\Exports;

use DB;
use App\Libro;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LibrosExport implements FromCollection,WithHeadings{
    /**
    * @return \Illuminate\Support\Collection
    */

    protected $editorial;
    
    public function __construct($editorial)
    {
        $this->editorial = $editorial;
    }

    public function headings(): array
    {
        return [
            'isbn', 
            'titulo',
            'editorial',
            'piezas',
            'defectuosos'
        ];
    }
    
    public function collection(){ 
        if($this->editorial === 'TODO'){
            $libros = DB::table('libros')
                    ->select('isbn', 'titulo', 'editorial', 'piezas', 'defectuosos')
                    ->where('estado', 'activo')
                    ->orderBy('editorial','asc')->get();
        }
        else{
            $libros = DB::table('libros')
                    ->select('isbn', 'titulo', 'editorial', 'piezas', 'defectuosos')
                    ->where('editorial', $this->editorial)
                    ->where('estado', 'activo')
                    ->orderBy('titulo','asc')->get();
        }
        return $libros;
    }
}
