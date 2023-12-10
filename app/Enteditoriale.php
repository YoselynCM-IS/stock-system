<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Entdeposito;

class Enteditoriale extends Model
{
    protected $fillable = [
        'id', 
        'editorial', 
        'total', 
        'total_devolucion', 
        'total_pagos',
        'total_pendiente'
    ];

    //Uno a muchos
    //Una deuda de editorial puede tener muchos depositos
    public function entdepositos(){
        return $this->hasMany(Entdeposito::class);
    }
}
