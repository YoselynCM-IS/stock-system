<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Foto extends Model
{
    protected $fillable = [
        'remdeposito_id', 'name', 'size', 'extension', 'public_url', 'creado_por'
    ];
}
