<?php

namespace App\Exports;

use Carbon\Carbon;
use App\Remisione;
use Luecano\NumeroALetras\NumeroALetras;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class RemisionExport implements FromView
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
        $remision = $this->get_remision();
        $fecha = new Carbon($remision->fecha_entrega);
        $formatter = new NumeroALetras();
        return view('download.excel.remisiones.remision', [
            'fecha' => $fecha,
            'remision' => $remision,
            'total_letras' => $formatter->toWords($remision->total, 2)
        ]);
    }

    public function get_remision(){
        return Remisione::whereId($this->id)->with(['cliente', 'datos.libro'])->first();
    }
}
