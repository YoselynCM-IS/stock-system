<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Remisione;
use App\Libro;

class Fecha extends Model
{
    use SoftDeletes; //Implementamos 

    protected $dates = ['deleted_at']; //Registramos la nueva columna

    protected $fillable = [
        'id', 
        'remisione_id',
        'pack_id',
        'fecha_devolucion',
        'libro_id', 
        'unidades',
        'total',
        'entregado_por',
        'creado_por',
        'defectuosos',
        'comentario'
    ];

    //Uno a muchos (inversa)
    //Una fecha solo puede pertencer a una remisión
    public function remision(){
        return $this->belongsTo(Remisione::class);
    }

    //Uno a muchos (Inversa)
    //Una fecha solo puede tener un libro
    public function libro(){
        return $this->belongsTo(Libro::class);
    }
}
