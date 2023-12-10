<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Libro;

class Prodevolucione extends Model
{
    protected $fillable = [
        'promotion_id', 
        'libro_id', 
        'unidades',
        'creado_por'
    ];

    //Uno a muchos (Inversa)
    //Una devolución solo puede tener un libro
    public function libro(){
        return $this->belongsTo(Libro::class);
    }
}
