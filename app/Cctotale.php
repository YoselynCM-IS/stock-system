<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Cliente;
use App\Corte;

class Cctotale extends Model
{
    protected $fillable = [
        'corte_id', 'cliente_id', 'total', 'total_devolucion', 'total_pagos',
        'total_pagar', 'total_favor', 'corte_id_favor'
    ];

    // 1 a muchas (Inversa)
    public function cliente(){
        return $this->belongsTo(Cliente::class);
    }

    // 1 a muchas (Inversa)
    public function corte(){
        return $this->belongsTo(Corte::class);
    }
}
