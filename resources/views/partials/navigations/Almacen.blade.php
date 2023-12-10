<li>
	<a class="nav-link" href="{{ route('information.remisiones.lista') }}">{{ __("Remisiones") }}</a>
</li>
<li>
	<a class="nav-link" href="{{ route('almacen.devoluciones') }}">{{ __("Devoluciones") }}</a>
</li>
<li>
	<a class="nav-link" href="{{ route('information.pedidos.proveedor') }}">{{ __("Pedidos") }}</a>
</li>
<li>
	<a class="nav-link" href="{{ route('information.entradas.lista') }}">{{ __("Entradas") }}</a>
</li>
<li>
	<a class="nav-link" href="{{ route('almacen.libros') }}">{{ __("Libros") }}</a>
</li>
<li>
	<a class="nav-link" href="{{ route('almacen.codes') }}">{{ __("Códigos") }}</a>
</li>
<li>
	<a class="nav-link" href="{{ route('almacen.entradas-salidas') }}">{{ __("Entradas / Salidas") }}</a>
</li>
@include('partials.navigations.logged')