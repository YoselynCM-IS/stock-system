<li class="nav-item dropdown">
	<a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
		Cortes <span class="caret"></span>
	</a>
	<div class="dropdown-menu dropdown-menu-left" aria-labelledby="navbarDropdown">
		<a class="dropdown-item" href="{{ route('manager.cortes.lista') }}">
			{{ __("Lista") }}
		</a>
		<a class="dropdown-item" href="{{ route('information.clientes.pagos') }}">
			{{ __("Pagos") }}
		</a>
		<a class="dropdown-item" href="{{ route('information.remisiones.lista') }}">
			{{ __('Remisiones') }}
		</a>
		<a class="dropdown-item" href="{{ route('information.devoluciones.lista') }}">
			{{ __('Devoluciones / Cerrar') }}
		</a>
		<a class="dropdown-item" href="{{ route('historial.remisiones.lista', 0) }}">
			{{ __("Historial") }}
		</a>
	</div>
</li>
<li class="nav-item dropdown">
	<a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
		Entradas <span class="caret"></span>
	</a>
	<div class="dropdown-menu dropdown-menu-left" aria-labelledby="navbarDropdown">
		<a class="dropdown-item" href="{{ route('information.entradas.lista') }}">
			{{ __("Lista") }}
		</a>
		<a class="dropdown-item" href="{{ route('information.entradas.pagos') }}">
			{{ __("Pagos") }}
		</a>
	</div>
</li>
<li class="nav-item dropdown">
	<a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
		Libros <span class="caret"></span>
	</a>
	<div class="dropdown-menu dropdown-menu-left" aria-labelledby="navbarDropdown">
		<a class="dropdown-item" href="{{ route('information.libros.lista') }}">{{ __("Lista") }}</a>
		<a class="dropdown-item" href="{{ route('information.libros.codes') }}">{{ __("Códigos") }}</a>
	</div>
</li>
<li>
	<a class="nav-link" href="{{ route('information.clientes.lista') }}">{{ __("Clientes") }}</a>
</li>
<li class="nav-item dropdown">
	<a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
		Pedidos <span class="caret"></span>
	</a>
	<div class="dropdown-menu dropdown-menu-left" aria-labelledby="navbarDropdown">
		<a class="dropdown-item" href="{{ route('information.pedidos.cliente') }}">{{ __("Cliente") }}</a>
		<a class="dropdown-item" href="{{ route('information.pedidos.proveedor') }}">{{ __("Proveedor") }}</a>
	</div>
</li>
<li class="nav-item dropdown">
	<a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
		Información <span class="caret"></span>
	</a>
	<div class="dropdown-menu dropdown-menu-left" aria-labelledby="navbarDropdown">
		<a class="dropdown-item" href="{{ route('information.actividades.lista') }}">
			{{ __("Actividades") }}
		</a>
		<a class="dropdown-item" href="{{ route('information.reportes.lista') }}">
			{{ __("Reportes") }}
		</a>
	</div>
</li>
<li class="nav-item dropdown">
	<a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
		Otros <span class="caret"></span>
	</a>
	<div class="dropdown-menu dropdown-menu-left" aria-labelledby="navbarDropdown">
		<a class="dropdown-item" href="{{ route('information.promotions.lista') }}">{{ __("Promociones") }}</a>
		<a class="dropdown-item" href="{{ route('information.donations.lista') }}">{{ __("Donaciones") }}</a>
		<a class="dropdown-item" href="{{ route('information.salidas.lista') }}">{{ __("Salidas") }}</a>
		<a class="dropdown-item" href="{{ route('manager.otros.notas') }}">{{ __("Notas") }}</a>
	</div>
</li>
<li class="nav-item dropdown">
	<a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
		Movimientos <span class="caret"></span>
	</a>
	<div class="dropdown-menu dropdown-menu-left" aria-labelledby="navbarDropdown">
		<a class="dropdown-item" href="{{ route('manager.movimientos.clientes') }}">
			{{ __("Clientes") }}
		</a>
		<a class="dropdown-item" href="{{ route('manager.movimientos.libros') }}">
			{{ __("Libros") }}
		</a>
		<a class="dropdown-item" href="{{ route('information.movimientos.entradas-salidas') }}">
			{{ __("Entradas / Salidas") }}
		</a>
		<a class="dropdown-item" href="{{ route('manager.remisiones.fecha_adeudo') }}">
			{{ __('Fecha de adeudos') }}
		</a>
	</div>
</li>
<user-notifications :user_id="{{auth()->user()->id}}" :noleidos="{{Auth::user()->unreadNotifications}}"></user-notifications>
@if(env('APP_NAME') == 'MAJESTIC EDUCATION')
	<li>
		<a class="nav-link" href="https://mestockexterno.com/login" target="_blank">{{ __("Querétaro") }}</a>
	</li>
@endif	
<li>
	<a class="nav-link" href="{{ route('manager.users.lista') }}">{{ __("Usuarios") }}</a>
</li>
@include('partials.navigations.logged')