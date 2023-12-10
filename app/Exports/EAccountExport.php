<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class EAccountExport implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */
    
    public function view(): View
    {
        $editoriales = \DB::table('enteditoriales')
                        ->select('editorial','total', 'total_pagos', 'total_devolucion', 'total_pendiente')
                        ->orderBy('editorial', 'asc')->get();
        $totales = $this->acumular_totales($editoriales);
        return view('download.excel.entradas.pagos', [
            'editoriales' => $editoriales,
            'totales' => $totales
        ]);
    }

    public function acumular_totales($editoriales){
        $totales = [
            'total' => 0,
            'total_pagos' => 0,
            'total_pendiente' => 0,
            'total_devolucion' => 0
        ];
        foreach($editoriales as $editorial){
            $totales = [
                'total' => $totales['total'] += $editorial->total,
                'total_pagos' => $totales['total_pagos'] += $editorial->total_pagos,
                'total_pendiente' => $totales['total_pendiente'] += $editorial->total_pendiente,
                'total_devolucion' => $totales['total_devolucion'] += $editorial->total_devolucion
            ];
        }
        return $totales;
    }

}
