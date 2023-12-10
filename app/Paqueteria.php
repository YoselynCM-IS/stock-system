<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Destinatario;

class Paqueteria extends Model
{
    protected $fillable = [
        'destinatario_id', 'paqueteria', 'guia', 'fecha_envio', 'tipo_envio', 'precio'
    ];

    public function destinatario(){
        return $this->belongsTo(Destinatario::class);
    }
}
