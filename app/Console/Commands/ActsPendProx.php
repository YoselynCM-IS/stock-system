<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Actividade;
use Carbon\Carbon;

class ActsPendProx extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'actividades:pendprox';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verificar las actividades pendientes y proximas';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $hoy = Carbon::today();
        Actividade::where('fecha', 'like', '%'.$hoy->format('Y-m-d').'%')
                        ->where('estado', 'proximo')
                        ->update(['estado' => 'pendiente']);

        // $mañana = Carbon::tomorrow();
        // Actividade::where('fecha', 'like', '%'.$mañana->format('Y-m-d').'%')
        //                 ->where('estado', 'pendiente')
        //                 ->update(['estado' => 'proximo']);
    }
}
