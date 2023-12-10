<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes; //línea necesaria
use Illuminate\Database\Eloquent\Model;
use App\Saldevolucione;
use App\Devolucione;
use App\Departure;
use App\Donacione;
use App\Register;
use App\Registro;
use App\Vendido;
use App\Salida;
use App\Fecha;
use App\Dato;

class Libro extends Model
{
    // use SoftDeletes; //Implementamos 

    // protected $dates = ['deleted_at']; //Registramos la nueva columna
    
    protected $fillable = [
        'id', 
        'ISBN',  
        'titulo', 
        'autor', 
        'editorial', 
        'edicion', 
        'piezas',
        'defectuosos',
        'estado',
        'type',
        'externo'
    ];

    //Uno a muchos
    //Un libro puede pertenecer a muchos registros de salida
    public function datos(){
        return $this->hasMany(Dato::class);
    }

    //Uno a muchos
    //Un libro puede pertenecer a muchas devoluciones 
    public function devoluciones(){
        return $this->hasMany(Devolucione::class);
    }

    //Uno a muchos
    //Un libro puede pertenecer a muchas saldevoluciones 
    public function saldevoluciones(){
        return $this->hasMany(Saldevolucione::class);
    }

    //Uno a muchos
    //Un libro puede pertenecer a muchos registros de entrada
    public function registros(){
        return $this->hasMany(Registro::class);
    }

    //Uno a muchos
    //Un libro puede pertenecer a muchas vendidos 
    public function vendidos(){
        return $this->hasMany(Vendido::class);
    }

    //Uno a muchos
    //Un libro puede pertenecer a muchos registros de nota
    public function registers(){
        return $this->hasMany(Register::class);
    }

    //Uno a muhcos
    //Un libro puede tener muchas salidas
    public function departures(){
        return $this->hasMany(Departure::class);
    }

    //Uno a muchos
    //Un libro puede pertenecer a muchas fechas 
    public function fechas(){
        return $this->hasMany(Fecha::class);
    }

    //Uno a muchos
    //Un libro puede tener muchas donaciones
    public function donaciones(){
        return $this->hasMany(Donacione::class);
    }

    //Uno a muchos
    //Un libro puede tener muchas salidas
    public function salidas(){
        return $this->hasMany(Salida::class);
    }
}
