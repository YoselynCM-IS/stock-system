<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Remcliente;
use App\Actividade;
use App\Remisione;
use App\Adeudo;
use App\Estado;
use App\Libro;
use App\User;

class Cliente extends Model
{
    protected $fillable = [
        'id', 'name', 'contacto', 'email', 'telefono', 'direccion', 'condiciones_pago', 'rfc', 'fiscal',
        'tipo', 'user_id', 'estado_id', 'tel_oficina'
    ];

    //Uno a muchos
    //Un cliente puede tener muchas remisiones
    public function remisiones(){
        return $this->hasMany(Remisione::class);
    }

    //Uno a muchos
    //Un cliente puede tener muchos adeudos
    public function adeudos(){
        return $this->hasMany(Adeudo::class);
    }

    //Uno a uno
    //Un cliente solo puede tener un remcliente
    public function remcliente(){
        return $this->hasOne(Remcliente::class);
    }

    // Uno a muchos (inversa)
    public function user(){
        return $this->belongsTo(User::class);
    }

    // Uno a muchos (inversa)
    public function estado(){
        return $this->belongsTo(Estado::class);
    }

    // Muchos a muchos
    public function libros(){
        return $this->belongsToMany(Libro::class)->withPivot('costo_unitario');
    }

    public function actividades(){
        return $this->belongsToMany(Actividade::class);
    }

}
