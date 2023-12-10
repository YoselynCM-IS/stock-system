<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Remisione;
use App\Dato;
use App\Libro;

class Devolucione extends Model
{
    protected $fillable = [
        'id', 
        'remisione_id', 
        'dato_id', 
        'libro_id',
        'unidades', 
        'unidades_resta',
        'unidades_base',
        'total_base',
        'total', 
        'total_resta'
    ];

    //Uno a muchos (inversa)
    //Una devolucion solo puede pertencer a una remisión
    public function remisione(){
        return $this->belongsTo(Remisione::class);
    }

    //Uno a uno
    //Una devolcuion solo puede pertenecer a un dato
    public function dato(){
        return $this->belongsTo(Dato::class);
    }

    //Uno a muchos (Inversa)
    //Una devolucion solo puede tener un libro
    public function libro(){
        return $this->belongsTo(Libro::class);
    }
}
