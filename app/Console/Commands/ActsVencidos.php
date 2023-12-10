<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Actividade;
use Carbon\Carbon;

class ActsVencidos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'actividades:vencidos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verificar que las atividades no hayan vencido';

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
        $hoy = Carbon::now();
        $observaciones = '<p><b>ACTIVIDAD VENCIDA - '.$hoy.'</b></p>';
            
        Actividade::where('estado', 'pendiente')
                        ->where('marcar_antesde', '<', $hoy)
                        ->update([
                            'estado' => 'vencido',
                            'observaciones' => $observaciones
                        ]);
    }
}
