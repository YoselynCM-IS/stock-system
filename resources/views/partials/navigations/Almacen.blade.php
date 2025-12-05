<li>
	<a class="nav-link" href="{{ route('information.remisiones.lista') }}">{{ __("Remisiones") }}</a>
</li>
<li>
	<a class="nav-link" href="{{ route('information.devoluciones.lista') }}">{{ __("Devoluciones") }}</a>
</li>
<li>
	<a class="nav-link" href="{{ route('information.pedidos.proveedor') }}">{{ __("Pedidos") }}</a>
</li>
<li>
	<a class="nav-link" href="{{ route('information.entradas.lista') }}">{{ __("Entradas") }}</a>
</li>
<li class="nav-item dropdown">
	<a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
		Inventario <span class="caret"></span>
	</a>
	<div class="dropdown-menu dropdown-menu-left" aria-labelledby="navbarDropdown">
		<a class="dropdown-item" href="{{ route('information.libros.lista') }}">{{ __("Sistema actual") }}</a>
		<a class="dropdown-item" href="{{ route('libro.all_sistemas') }}">{{ __("Sistemas (ME / OB)") }}</a>
		<a class="dropdown-item" href="{{ route('information.libros.codes') }}">{{ __("Códigos") }}</a>
	</div>
</li>
<li class="nav-item dropdown">
	<a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
		Otros <span class="caret"></span>
	</a>
	<div class="dropdown-menu dropdown-menu-left" aria-labelledby="navbarDropdown">
		<a class="dropdown-item" href="{{ route('information.promotions.lista') }}">
			{{ __('Promociones') }}
		</a>
		<a class="dropdown-item" href="{{ route('information.donations.lista') }}">
			{{ __('Donaciones') }}
		</a>
	</div>
</li>
<li>
	<a class="nav-link" href="{{ route('information.movimientos.entradas-salidas') }}">{{ __("Entradas / Salidas") }}</a>
</li>
@include('partials.navigations.logged')