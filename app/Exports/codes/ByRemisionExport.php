<?php

namespace App\Exports\codes;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ByRemisionExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */

    protected $remisione_id;
    
    public function __construct($remisione_id)
    {
        $this->remisione_id = $remisione_id;
    }

    public function headings(): array
    {
        return [
            'ISBN', 
            'LIBRO',
            'CÓDIGO 1',
            'CÓDIGO 2'
        ];
    }

    public function collection(){ 
        $code_dato = \DB::table('code_dato')
                        ->join('codes', 'code_dato.code_id', '=', 'codes.id')
                        ->join('datos', 'code_dato.dato_id', '=', 'datos.id')
                        ->join('libros', 'datos.libro_id', '=', 'libros.id')
                        ->where('datos.remisione_id', $this->remisione_id)
                        ->where('code_dato.devolucion', 0)
                        ->select('libros.ISBN', 'libros.titulo', 'codes.codigo')
                        ->get();
        
        $codigos = collect();
        $code_dato->map(function($cd) use(&$codigos){
            if(strstr($cd->codigo, 'CÓDIGO 1:')){
                $c = $cd->codigo;
                $p1 = substr($c, strpos($c, 'CÓDIGO 1:') + 11);
                $sp = strpos($p1, ' / ');

                $codigo1 = substr($p1, 0, $sp);
                $codigo2 = substr($p1, $sp + 14);

                $codigos->push([
                    'ISBN' => $cd->ISBN,
                    'titulo' => $cd->titulo,
                    'codigo' => $codigo1,
                    'codigo2' => $codigo2
                ]);
            } else {
                $codigos->push([
                    'ISBN' => $cd->ISBN,
                    'titulo' => $cd->titulo,
                    'codigo' => $cd->codigo
                ]);
            }
        });

        return $codigos;
    }
}
