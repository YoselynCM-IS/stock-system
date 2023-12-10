<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Libro;

class Saldevolucione extends Model
{
    //Uno a muchos (Inversa)
    //Una saldevolucion solo puede tener un libro
    public function libro(){
        return $this->belongsTo(Libro::class);
    }
}
