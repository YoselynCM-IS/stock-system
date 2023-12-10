<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Peticione;
use App\Cliente;
use App\Order;
Use App\User;

class Pedido extends Model
{
    protected $fillable = [
        'user_id',
        'cliente_id', 
        'total_quantity',
        'total',
        'total_solicitar',
        'estado',
        'comentarios'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function cliente(){
        return $this->belongsTo(Cliente::class);
    }

    public function peticiones(){
        return $this->hasMany(Peticione::class);
    }

    public function orders(){
        return $this->hasMany(Order::class);
    }
}
