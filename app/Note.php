<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Register;
use App\Repayment;

class Note extends Model
{
    protected $fillable = [
        'id', 
        'folio',
        'cliente', 
        'total_salida',  
        'total_devolucion', 
        'total_pagar', 
        'pagos', 
        'fecha_devolucion',
        'entregado_por',
        'creado_por'
    ];

    //Uno a muchos
    //Una nota puede tener muchos registros
    public function registers(){
        return $this->hasMany(Register::class);
    }

    //Uno a uno
    //Una nota puede tener muchas devoluciones
    public function repayments(){
        return $this->hasMany(Repayment::class);
    }
}
