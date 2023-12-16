<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Corte;

class Adeudo extends Model
{
    use HasFactory;
    protected $fillable = ['cliente_id', 'corte_id', 'remdeposito_id', 'saldo_inicial', 'saldo_pagado', 'saldo_pendiente', 'dias', 'rango', 'ingresado_por'];
    
    // 1 a muchas (Inversa)
    public function corte(){
        return $this->belongsTo(Corte::class);
    }

    //Uno a muchos
    public function abonos(){
        return $this->hasMany(Abono::class);
    }
}
