<?php

namespace App\Exports\promociones;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\Promotion;

class PromotionExport implements FromView
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
        $promotion = Promotion::whereId($this->id)->with('departures.libro', 'departures.codes')->first();
        return view('download.excel.promotions.promotion', [
            'promotion' => $promotion
        ]);
    }
}
