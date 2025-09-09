<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class ActualizarTipoCambio extends Command
{
    /**
     * El nombre y firma del comando (ejecutar con php artisan tipo-cambio:actualizar).
     */
    protected $signature = 'tipo-cambio:actualizar';

    /**
     * Descripción.
     */
    protected $description = 'Obtiene el tipo de cambio FIX de Banxico y lo guarda en la base de datos';

    public function handle()
    {
        $response = Http::withHeaders([
            'Bmx-Token' => env('BANXICO_TOKEN'),
        ])->get('https://www.banxico.org.mx/SieAPIRest/service/v1/series/SF43718/datos/oportuno');

        if ($response->failed()) {
            $this->error('Error al consultar Banxico');
            return 1;
        }

        $data = $response->json();

        $serie = $data['bmx']['series'][0]['datos'][0] ?? null;

        if (!$serie) {
            $this->error('No se encontró información en la respuesta de Banxico');
            return 1;
        }

        $valor = (float) $serie['dato'];

        DB::table('monedas')->where('codigo', 'USD')->update([
            'valor' => $valor, 
            'created_at' => now(),
            'updated_at' => now()
        ]);
        return 0;
    }
}
