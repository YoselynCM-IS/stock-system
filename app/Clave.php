<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clave extends Model
{
    use HasFactory;
    protected $fillable = [
        'libro_id', 'tipo', 'piezas' 
    ];
}
