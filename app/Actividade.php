<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Cliente;
use App\User;

class Actividade extends Model
{
    protected $fillable = [
        'id', 'user_id', 'nombre', 'tipo', 'descripcion', 'estado', 'fecha', 'lugar',
        'exitosa', 'observaciones', 'recordatorio', 'marcar_antesde'
    ];   

    public function clientes(){
        return $this->belongsToMany(Cliente::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
}
