<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Paqueteria;
use App\Cliente;
use App\Dato;
use App\Devolucione;
use App\Vendido;
use App\Deposito;
use App\Comentario;
use App\Fecha;
use App\Order;

class Remisione extends Model
{
    use SoftDeletes; //Implementamos 

    protected $dates = ['deleted_at']; //Registramos la nueva columna
    
    protected $fillable = [
        'id', 
        'user_id',
        'cliente_id',
        'corte_id', 
        'paqueteria_id',
        'tipo', 
        'total', 
        'total_devolucion', 
        'total_donacion',
        'total_pagar', 
        'pagos',
        'fecha_entrega', 
        'estado', 
        'fecha_creacion',
        'fecha_devolucion',
        'responsable',
        'cerrado_por'
    ];

    //Uno a muchos (Inversa)
    //Una remision solo puede pertenecer a un cliente
    public function cliente(){
        return $this->belongsTo(Cliente::class);
    }

    //Uno a uno
    //Un usuario solo puede tener un rol
    public function paqueteria(){
        return $this->belongsTo(Paqueteria::class);
    }

    //Uno a muchos
    //UNa remision puede tener muchos datos
    public function datos(){
        return $this->hasMany(Dato::class);
    }

    //Uno a muchos
    //UNa remision puede tener muchas devoluciones
    public function devoluciones(){
        return $this->hasMany(Devolucione::class);
    }

    //Uno a muchos
    //Una remision puede tener muchas vendidos
    public function vendidos(){
        return $this->hasMany(Vendido::class);
    }

    //Uno a muchos
    //Una remision puede tener muchos depositos
    public function depositos(){
        return $this->hasMany(Deposito::class);
    }

    // 10 - 10- 19
    //Uno a muchos
    //Una remision puede tener muchas fechas de devolucion
    public function fechas(){
        return $this->hasMany(Fecha::class);
    }

    //Uno a muchos
    //UNa remision puede tener muchos comentarios
    public function comentarios(){
        return $this->hasMany(Comentario::class);
    }

    // Muchos a muchos
    public function orders(){
        return $this->belongsToMany(Order::class);
    }
}
