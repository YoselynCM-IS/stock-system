<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Regalo;
use App\Libro;
use App\Code;

class Donacione extends Model
{
    protected $fillable = [
        'id', 
        'regalo_id',
        'libro_id',
        'unidades'
    ];

    //Uno a muchos (inversa)
    //Una donacion solo puede pertencer a una remisión
    public function regalo(){
        return $this->belongsTo(Regalo::class);
    }

    //Uno a muchos (Inversa)
    //Una donacion solo puede tener un libro
    public function libro(){
        return $this->belongsTo(Libro::class);
    }

    // Muchos a muchos
    public function codes(){
        return $this->belongsToMany(Code::class);
    }
}
