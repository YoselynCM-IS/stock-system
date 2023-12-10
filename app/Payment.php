<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Register;

class Payment extends Model
{
    protected $fillable = [
        'id', 
        'register_id',
        'unidades',
        'pago'
    ];

    //Uno a muchos (Inversa)
    //Un pago solo puede pertenecer a un registro
    public function register(){
        return $this->belongsTo(Register::class);
    }
}
