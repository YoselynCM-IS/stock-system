<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Libro;

class Clave extends Model
{
    use HasFactory;
    protected $fillable = [
        'libro_id', 'tipo', 'piezas' 
    ];

    //Uno a muchos (Inversa)
    //Un codigo solo puede tener un libro
    public function libro(){
        return $this->belongsTo(Libro::class);
    }
}
