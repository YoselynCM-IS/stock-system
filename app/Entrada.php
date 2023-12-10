<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Entdevolucione;
use App\Comprobante;
use App\Repayment;
use App\Registro;
use App\Imprenta;

class Entrada extends Model
{
    protected $fillable = [
        'id', 
        'corte_id',
        'folio',
        'editorial',
        'imprenta_id',
        'unidades', 
        'total',
        'total_pagos',
        'total_devolucion',
        'lugar',
        'creado_por'
    ];

    //Uno a muchos
    //Una entrada puede tener muchos registros
    public function registros(){
        return $this->hasMany(Registro::class);
    }

    //Uno a muchos
    //Una entrada puede tener muchos pagos
    public function repayments(){
        return $this->hasMany(Repayment::class);
    }

    //Uno a muchos
    //Una entrada puede tener muchas devoluciones
    public function entdevoluciones(){
        return $this->hasMany(Entdevolucione::class);
    }

    public function imprenta(){
        return $this->belongsTo(Imprenta::class);
    }

    public function comprobantes(){
        return $this->hasMany(Comprobante::class);
    }

}
