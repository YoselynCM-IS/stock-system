<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Cliente;
use App\User;

class Seguimiento extends Model
{
    protected $fillable = ['user_id', 'cliente_id', 'tipo', 'situacion', 'respuesta', 'fecha_hora', 'duracion', 'comentario'];

    public function cliente(){
        return $this->belongsTo(Cliente::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
}
