<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pack extends Model
{
    protected $fillable = [
        'libro_fisico', 'libro_digital', 'piezas', 'defectuosos'
    ];
}
