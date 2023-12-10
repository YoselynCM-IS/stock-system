<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Corte;

class Ectotale extends Model
{
    protected $fillable = [
        'corte_id', 'editoriale_id', 'total', 'total_devolucion', 'total_pagos',
        'total_pagar', 'total_favor', 'corte_id_favor'
    ];

    // 1 a muchas (Inversa)
    public function corte(){
        return $this->belongsTo(Corte::class);
    }
}
