<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Surtido extends Model
{
    use HasFactory;

    protected $fillable = [ 'pedido_id', 'relacion_tabla', 'relacion_id', 'comentario' ];
}
