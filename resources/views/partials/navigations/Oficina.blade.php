<li class="nav-item dropdown">
	<a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
		Remisiones <span class="caret"></span>
	</a>
	<div class="dropdown-menu dropdown-menu-left" aria-labelledby="navbarDropdown">
		<a class="dropdown-item" href="{{ route('information.remisiones.lista') }}">
			{{ __('Lista') }}
		</a>
		<a class="dropdown-item" href="{{ route('oficina.cerrar') }}">
			{{ __('Devoluciones / Cerrar') }}
		</a>
	</div>
</li>
<li>
	<a class="nav-link" href="{{ route('oficina.pagos') }}">{{ __("Pagos") }}</a>
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
		Entradas <span class="caret"></span>
	</a>
	<div class="dropdown-menu dropdown-menu-left" aria-labelledby="navbarDropdown">
		<a class="dropdown-item" href="{{ route('oficina.entradas') }}">
			{{ __('Lista') }}
		</a>
		<a class="dropdown-item" href="{{ route('oficina.entradas.pagos') }}">
			{{ __('Pagos') }}
		</a>
	</div>
</li>
<li class="nav-item dropdown">
	<a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
		Libros <span class="caret"></span>
	</a>
	<div class="dropdown-menu dropdown-menu-left" aria-labelledby="navbarDropdown">
		<a class="dropdown-item" href="{{ route('oficina.libros') }}">{{ __("Lista") }}</a>
		<a class="dropdown-item" href="{{ route('oficina.codes') }}">{{ __("Códigos") }}</a>
	</div>
</li>
<li>
	<a class="nav-link" href="{{ route('information.clientes.lista') }}">{{ __("Clientes") }}</a>
</li>
<li class="nav-item dropdown">
	<a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
		Otros <span class="caret"></span>
	</a>
	<div class="dropdown-menu dropdown-menu-left" aria-labelledby="navbarDropdown">
		<a class="dropdown-item" href="{{ route('oficina.promociones') }}">
			{{ __('Promociones') }}
		</a>
		<a class="dropdown-item" href="{{ route('oficina.donaciones') }}">
			{{ __('Donaciones') }}
		</a>
		<a class="dropdown-item" href="{{ route('oficina.salidas') }}">
			{{ __('Salidas') }}
		</a>
		<a class="dropdown-item" href="{{ route('oficina.entradas-salidas') }}">
			{{ __("Entradas / Salidas") }}
		</a>
	</div>
</li>
@if(env('APP_NAME') == 'MAJESTIC EDUCATION')
	<li>
		<a class="nav-link" href="https://mestockexterno.com/login" target="_blank">{{ __("Querétaro") }}</a>
	</li>
@endif
<user-notifications :user_id="{{auth()->user()->id}}" :noleidos="{{Auth::user()->unreadNotifications}}"></user-notifications>
@include('partials.navigations.logged')