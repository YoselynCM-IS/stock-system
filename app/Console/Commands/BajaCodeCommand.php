<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Code;

class BajaCodeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'codes:baja';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Eliminar los codigos demo al cumplir 3 meses';

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
        $codes = Code::where('tipo', 'demo')->whereNotIn('estado', ['eliminado'])->get();
        $codes->map(function($code) use(&$hoy){
            if($hoy->diffInWeeks($code->created_at) >= 10){
                $code->update(['estado' => 'eliminado']);
            }
        });
    }
}
