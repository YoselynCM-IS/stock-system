<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Salida;
use App\Libro;

class Sregistro extends Model
{
    protected $fillable = [
        'id', 
        'salida_id',
        'libro_id', 
        'unidades'
    ];

    //Uno a muchos (Inversa)
    //Un registro solo puede tener un libro
    public function libro(){
        return $this->belongsTo(Libro::class);
    }

    // Uno a muchos (Inversa)
    // Un registro solo puede pertencer a una salida
    public function salida(){
        return $this->belongsTo(Salida::class);
    }
}
