<?php

namespace App\Exports\salidas;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\Salida;

class SalidaExport implements FromView
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct($id)
    {
        $this->id = $id;
    }

    public function view(): View
    {
        $salida = Salida::whereId($this->id)->with(['sregistros.libro'])->first();
        return view('download.excel.salidas.salida', [
            'salida' => $salida
        ]);
    }
}
