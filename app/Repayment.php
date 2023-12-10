<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Entrada;

class Repayment extends Model
{
    protected $fillable = [
        'id', 
        'entrada_id',
        'pago', 
    ];

    //Uno a muchos (Inversa)
    //Un repayment solo puede pertenecer a una entrada
    public function entrada(){
        return $this->belongsTo(Entrada::class);
    }
}
