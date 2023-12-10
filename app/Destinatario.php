<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Destinatario extends Model
{
    protected $fillable = [ 
        'destinatario', 'rfc', 'direccion', 'regimen_fiscal', 'telefono' 
    ];
}
