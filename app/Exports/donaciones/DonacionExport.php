<?php

namespace App\Exports\donaciones;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\Regalo;

class DonacionExport implements FromView
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
        $regalo = Regalo::whereId($this->id)->with('donaciones.libro')->first();
        return view('download.excel.regalos.donation', [
            'regalo' => $regalo
        ]);
    }
}
