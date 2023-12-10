<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Notifications\RecordarActNotification;
use App\Actividade;
use Carbon\Carbon;
use App\User;

class RecordarActCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'actividades:recordatorio';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Emitir notificación de recordatorio de la actividad';

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
            
        $actividades = Actividade::where('estado', 'pendiente')
                        ->where('recordatorio', '<', $hoy)
                        ->where('fecha', '>', $hoy)
                        ->get();

        $users = User::whereIn('role_id', [5,6,7])->get();
        foreach($actividades as $actividad){
            foreach($users as $user){
                $user->notify(new RecordarActNotification($actividad));
            }
        }
    }
}
