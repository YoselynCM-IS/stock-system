<?php

namespace App\Exports\entradas;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\Entrada;

class EntradaExport implements FromView
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
        $entrada = Entrada::whereId($this->id)->with(['registros.libro', 'repayments'])->first();
        return view('download.excel.entradas.entrada', [
            'entrada' => $entrada
        ]);
    }
}
