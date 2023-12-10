<?php

namespace App\Exports\actividades;

use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;
use App\Actividade;

class ActividadExport implements FromView
{
    public function __construct($id)
    {
        $this->id = $id;
    }

    public function view(): View
    {
        $actividad = Actividade::where('id', $this->id)->with('clientes', 'user')->first();
        return view('download.excel.actividades.actividad', [
            'actividad' => $actividad
        ]);
    }
}
