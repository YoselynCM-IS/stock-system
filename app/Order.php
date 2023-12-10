<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Remisione;
use App\Element;
use App\Pedido;

class Order extends Model
{
    protected $fillable = [
        'id',
        'tipo',
        'almacen',
        'pedido_id',
        'cliente_id',
        'identifier', 
        'date',
        'provider',
        'destination',  
        'total_bill', 
        'status', 
        'observations', 
        'actual_total_bill',
        'creado_por'
    ];

    public function elements(){
        return $this->hasMany(Element::class);
    }

    public function pedido(){
        return $this->belongsTo(Pedido::class);
    }

    // Muchos a muchos
    public function remisiones(){
        return $this->belongsToMany(Remisione::class);
    }
}
