<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;
use App\Remdeposito;
use App\Remcliente;
use App\Remisione;
use Carbon\Carbon;
use App\Deposito;
use App\Fecha;

class EdoCuentaExport implements FromView
{
    protected $cliente_id;

    public function __construct($cliente_id){
        $this->cliente_id = $cliente_id;
    }

    public function view(): View{
        $remcliente = Remcliente::where('cliente_id', $this->cliente_id)
                        ->with('cliente')->first();
        $remisiones = Remisione::where('cliente_id', $this->cliente_id)
                    ->orderBy('created_at', 'desc')->get();
    
        $ids = [];
        $remisiones->map(function($remision) use(&$ids){
            $ids[] = $remision->id;
        });

        $remdepositos = Remdeposito::where('remcliente_id', $remcliente->cliente_id)
                    ->orderBy('created_at', 'desc')->get();

        $fechas = Fecha::whereIn('remisione_id', $ids)->with('libro')
                    ->orderBy('created_at', 'desc')->get();
        $depositos = Deposito::whereIn('remisione_id', $ids)
                    ->orderBy('created_at', 'desc')->get();

        return view('download.excel.pagos.edo_cuenta', [
            'date' => Carbon::now(),
            'remcliente' => $remcliente,
            'remisiones' => $remisiones,
            'remdepositos' => $remdepositos,
            'fechas' => $fechas,
            'depositos' => $depositos
        ]);
    }
}
