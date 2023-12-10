<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Saldevolucione;
use App\Sregistro;

class Salida extends Model
{
    protected $fillable = [
        'id', 
        'creado_por',
        'folio',
        'unidades',
        'unidades_devolucion',
        'estado'
    ];

    //Uno a muchos
    //Una salida puede tener muchos registros
    public function sregistros(){
        return $this->hasMany(Sregistro::class);
    }

    //Uno a muchos
    //Una salida puede tener muchas devoluciones
    public function saldevoluciones(){
        return $this->hasMany(Saldevolucione::class);
    }
}
