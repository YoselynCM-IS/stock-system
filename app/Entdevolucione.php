<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Registro;
use App\Entrada;

class Entdevolucione extends Model
{
    protected $fillable = [
        'id', 
        'entrada_id', 
        'registro_id', 
        'unidades', 
        'total',
        'creado_por'
    ];

    //Uno a muchos (inversa)
    //Una devolución solo puede pertencer a una entrada
    public function entrada(){
        return $this->belongsTo(Entrada::class);
    }
    
    public function registro(){
        return $this->belongsTo(Registro::class);
    }
}
