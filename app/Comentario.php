<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Remisione;
use App\User;

class Comentario extends Model
{
    protected $fillable = [
        'id', 
        'remisione_id', 
        'user_id',
        'comentario' 
    ];

    //Uno a muchos (inversa)
    //Un comentario solo puede pertencer a una remisión
    public function remisione(){
        return $this->belongsTo(Remisione::class);
    }

    //Uno a muchos (Inverso)
    //Un comentario solo puede pertenecer a un usuario
    public function user(){
        return $this->belongsTo(User::class);
    }
}
