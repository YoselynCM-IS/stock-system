<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Abono extends Model
{
    use HasFactory;
    protected $fillable = ['adeudo_id', 'fecha', 'pago', 'nota', 'ingresado_por'];

}
