<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comprobante extends Model
{
    protected $fillable = [
        'entrada_id', 'name', 'size', 'extension', 'public_url'
    ];
}
