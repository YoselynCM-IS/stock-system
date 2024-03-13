<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Prodevolucione;
use App\Paqueteria;
use App\Departure;
use App\Cliente;

class Promotion extends Model
{
    protected $fillable = [
        'id', 
        'cliente_id',
        'paqueteria_id',
        'folio',
        'plantel',
        'descripcion', 
        'unidades',  
        'unidades_devolucion',
        'unidades_pendientes',
        'entregado_por',
        'estado',
        'creado_por'
    ];

    //Uno a muchos
    //Una promoción puede tener muchas salidas
    public function departures(){
        return $this->hasMany(Departure::class);
    }

    //Uno a muchos
    //Una promoción puede tener muchas devoluciones
    public function prodevoluciones(){
        return $this->hasMany(Prodevolucione::class);
    }

    public function cliente(){
        return $this->belongsTo(Cliente::class);
    }

    //Uno a uno
    public function paqueteria(){
        return $this->belongsTo(Paqueteria::class);
    }
}
