<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Repayment;
use App\Payment;
use App\Libro;
use App\Note;

class Register extends Model
{
    protected $fillable = [
        'id', 
        'note_id', 
        'libro_id', 
        'costo_unitario',
        'unidades', 
        'total',
        'unidades_pagado',
        'total_pagado',
        'unidades_devuelto',
        'total_devuelto',
        'unidades_pendiente',
        'total_pendiente',
        'unidades_base',
        'total_base'
    ];


    //Uno a muchos (Inversa)
    //Un registro solo puede pertenecer a una nota
    public function note(){
        return $this->belongsTo(Note::class);
    }

    //Uno a muchos (Inversa)
    //Un registro solo puede tener un libro
    public function libro(){
        return $this->belongsTo(Libro::class);
    }

    //Uno a muchos
    //Un registro puede tener muchos pagos
    public function payments(){
        return $this->hasMany(Payment::class);
    }

    //Uno a uno
    //Un register solo puede tener una devolucion
    public function repayment(){
        return $this->hasOne(Repayment::class);
    }
}
