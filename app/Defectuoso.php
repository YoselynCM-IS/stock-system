<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Defectuoso extends Model
{
    protected $fillable = [
        'libro_id', 'numero', 'comentario'
    ];
}
