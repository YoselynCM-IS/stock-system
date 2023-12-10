<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes; //línea necesaria
use Illuminate\Database\Eloquent\Model;
use App\Entdevolucione;
use App\Entrada;
use App\Libro;
use App\Code;

class Registro extends Model
{
    use SoftDeletes; //Implementamos 

    protected $dates = ['deleted_at']; //Registramos la nueva columna

    protected $fillable = [
        'id', 
        'entrada_id', 
        'libro_id',
        'costo_unitario', 
        'unidades', 
        'unidades_que',
        'total',
        'unidades_pendientes',
        'unidades_base',
        'total_base'
    ];

    //Uno a muchos (inversa)
    //Un registro solo puede pertencer a una entrada
    public function entrada(){
        return $this->belongsTo(Entrada::class);
    }

    //Uno a muchos (Inversa)
    //Un registro solo puede tener un libro
    public function libro(){
        return $this->belongsTo(Libro::class);
    }

    //Uno a muchos
    //Una entrada puede tener muchas devoluciones
    public function entdevoluciones(){
        return $this->hasMany(Entdevolucione::class);
    }
    
    // Muchos a muchos
    public function codes(){
        return $this->belongsToMany(Code::class)->withPivot('devolucion');
    }
}
