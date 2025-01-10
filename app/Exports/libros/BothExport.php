<?php

namespace App\Exports\libros;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Libro;
use App\Pack;
use App\Code;

class BothExport implements FromCollection, WithHeadings
{
    public function headings(): array
    {
        return [
            'Tipo', 'ISBN', 'Titulo', 
            'Piezas (ME)', 'Scratch (ME)', 'Digital/Físico (ME)', 'Defectuosos (ME)',
            'Piezas (OB)', 'Scratch (OB)', 'Digital/Físico (OB)', 'Defectuosos (OB)',
            'Piezas (TODO)', 'Scratch (TODO)', 'Digital/Físico (TODO)', 'Defectuosos (TODO)'
        ];
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $s1 = Libro::orderBy('editorial', 'asc')
                        ->orderBy('titulo', 'asc')
                        ->where('estado', 'activo')
                        ->where('editorial', 'MAJESTIC EDUCATION')->get();
        $s2 = Libro::on('opuesto')->orderBy('editorial', 'asc')
                        ->orderBy('titulo', 'asc')
                        ->where('estado', 'activo')
                        ->where('editorial', 'MAJESTIC EDUCATION')->get();
        
        return $this->organizar_todo($s1, $s2);
    }

    public function organizar_todo($s1, $s2){
        $ls = collect();
        $s1->map(function($libro) use(&$ls, $s2){
            $data1 = $this->get_only_scratch('mysql', $libro->id, $libro->piezas, $libro->type);
            $sum_scratch1 = (int) $data1['sum_scratch'];
            $count_solo1 = (int) $data1['count_solo'];

            $dato = [
                'ISBN' => $libro->ISBN,
                'titulo' => $libro->titulo,
                'type' => $libro->type,
                'editorial' => $libro->editorial,
                'piezas_1' => $libro->piezas,
                'scratch1' => $sum_scratch1,
                'solo1' => $count_solo1,
                'defectuosos_1' => $libro->defectuosos,
                'piezas_2' => 0,
                'scratch2' => 0,
                'solo2' => 0,
                'defectuosos_2' => 0,
                'total_piezas' => $libro->piezas, 
                'total_scratch' => $sum_scratch1, 
                'total_solo' => $count_solo1, 
                'total_defectuosos' => $libro->defectuosos
            ];

            $s2->map(function($opuesto) use ($libro, &$dato){
                if($opuesto->titulo == $libro->titulo){
                    $data2 = $this->get_only_scratch('opuesto', $opuesto->id, $opuesto->piezas, $opuesto->type);
                    $sum_scratch2 = (int) $data2['sum_scratch'];
                    $count_solo2 = (int) $data2['count_solo'];
                    $dato['piezas_2'] = $opuesto->piezas;
                    $dato['scratch2'] = $sum_scratch2;
                    $dato['solo2'] = $count_solo2;
                    $dato['defectuosos_2'] = $opuesto->defectuosos;
                    $dato['total_piezas'] = $dato['total_piezas'] + $opuesto->piezas;
                    $dato['total_scratch'] = $dato['total_scratch'] + $sum_scratch2;
                    $dato['total_solo'] = $dato['total_solo'] + $count_solo2;
                    $dato['total_defectuosos'] = $dato['total_defectuosos'] + $opuesto->defectuosos;
                }
            });

            $ls->push($dato);
        });
        return $ls;
    }

    // OBTENER SOLOS Y SCRATCH
    public function get_only_scratch($sistema, $id, $piezas, $type){
        $sum_scratch = 0;
        $count_solo = $piezas;
        if($type != 'promocion'){
            $sum_scratch = Pack::on($sistema)->where('libro_fisico', $id)
                    ->OrWhere('libro_digital', $id)
                    ->sum('piezas');

            if($type == 'digital') {
                $count_solo = Code::on($sistema)->where('libro_id', $id)
                                ->where('tipo', 'alumno')
                                ->where('estado', 'inventario')->count();
            }
            if($type == 'venta')
                $count_solo = $piezas - $sum_scratch;
        }
        return [
            'sum_scratch' => $sum_scratch, 
            'count_solo' => $count_solo,  
        ];
    }
}
