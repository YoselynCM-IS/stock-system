<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Pedido;
use App\Libro;

class Peticione extends Model
{
    use SoftDeletes; //Implementamos 

    protected $dates = ['deleted_at']; //Registramos la nueva columna
    
    protected $fillable = [
        'pedido_id',
        'libro_id', 
        'tipo',
        'quantity',
        'price',
        'total',
        'existencia',
        'faltante',
        'solicitar'
    ];

    public function libro(){
        return $this->belongsTo(Libro::class);
    }

    //Uno a muchos (inversa)
    //Una peticion solo puede pertencer a un pedido
    public function pedido(){
        return $this->belongsTo(Pedido::class);
    }
}
