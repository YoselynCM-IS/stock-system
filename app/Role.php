<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;

class Role extends Model
{
    //Uno a uno (Inversa)
    //Un role puede tener un solo usuario
    public function user(){
        return $this->hasOne(User::class);
    }
}
