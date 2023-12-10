<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Comentario;
use App\Role;
use App\Pago;

class User extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'role_id', 'name', 'user_name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    //Uno a uno
    //Un usuario solo puede tener un rol
    public function role(){
        return $this->belongsTo(Role::class);
    }

    //Uno a muchos
    //Una usuario puede tener muchos pagos
    public function pagos(){
        return $this->hasMany(Pago::class);
    }

    //Uno a muchos
    //UNa usuario puede tener muchos comentarios
    public function comentarios(){
        return $this->hasMany(Comentario::class);
    }

    public static function navigation(){
        return auth()->check() ? auth()->user()->role->rol : 'guest';
        //Con la función check Verifica si el usuario esta o no autentficado
        //Si si esta autentificado accede y mediante un objeto de la clase user (en este caso user()) accede al metodo rol() y extrae el nombre dle rol, de no ser asi sera invitado
    }
}
