<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Remdeposito;
use App\Cliente;

class Remcliente extends Model
{
    protected $fillable = [
        'id', 
        'cliente_id', 
        'total',
        'total_devolucion',
        'total_pagos',
        'total_pagar'
    ];

    //Uno a uno (Inversa)
    //Una deuda solo puede pertenecer a un cliente
    public function cliente(){
        return $this->belongsTo(Cliente::class);
    }

    //Uno a muchos
    //Una deuda puede tener muchos depositos
    public function remdepositos(){
        return $this->hasMany(Remdeposito::class);
    }
}
