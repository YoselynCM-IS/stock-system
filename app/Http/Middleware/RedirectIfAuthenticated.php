<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->check()) {
            if(auth()->user()->role_id == 5){
                return redirect('/information/actividades/lista');
            }
            if(auth()->user()->role_id == 7){
                return redirect('/information/actividades/simple');
            }
            return redirect('/information/remisiones/lista');
            // if(Auth::user()->role_id == 1){
            //     return redirect('/administrador/remisiones');
            // }
            // if(Auth::user()->role_id == 2){
            //     return redirect('/oficina/remisiones');
            // }
            // if(Auth::user()->role_id == 3){
            //     return redirect('/almacen/remisiones');
            // }
            // if(Auth::user()->role_id == 4){
            //     return redirect('/contador/remisiones');
            // }
            // if(Auth::user()->role_id == 5){
            //     return redirect('/information/remisiones/lista');
            // }
            // if(Auth::user()->role_id == 6){
            //     return redirect('/manager/remisiones/lista');
            // }
            // if(Auth::user()->role_id == 7){
            //     return redirect('/historial/remisiones/lista/0');
            // }
        }

        return $next($request);
    }
}
