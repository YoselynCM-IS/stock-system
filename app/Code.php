<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Departure;
use App\Donacione;
use App\Registro;
use App\Libro;
use App\Dato;

class Code extends Model
{
    protected $fillable = [
        'id', 
        'libro_id', 
        'codigo',
        'tipo',
        'estado'
    ];

    //Uno a muchos (Inversa)
    //Un codigo solo puede tener un libro
    public function libro(){
        return $this->belongsTo(Libro::class);
    }

    // Muchos a muchos
    public function datos(){
        return $this->belongsToMany(Dato::class)->withPivot('devolucion');
    }

    // Muchos a muchos
    public function registros(){
        return $this->belongsToMany(Registro::class)->withPivot('devolucion');
    }

    // Muchos a muchos
    public function departures(){
        return $this->belongsToMany(Departure::class);
    }

    // Muchos a muchos
    public function donaciones(){
        return $this->belongsToMany(Donacione::class);
    }
}
