<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Entdeposito extends Model
{
    use SoftDeletes; //Implementamos 

    protected $dates = ['deleted_at']; //Registramos la nueva columna
    
    protected $fillable = [
        'id', 
        'corte_id',
        'enteditoriale_id', 
        'pago',
        'fecha',
        'nota',
        'ingresado_por',
        'name', 'size', 'extension', 'public_url'
    ];
}
