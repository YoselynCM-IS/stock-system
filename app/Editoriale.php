<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Ectotale;

class Editoriale extends Model
{
    protected $fillable = [
        'editorial'
    ];

    public function ectotales(){
        return $this->hasMany(Ectotale::class);
    }
}
