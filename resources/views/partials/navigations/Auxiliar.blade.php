<li class="nav-item dropdown">
	<a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
		Inventario <span class="caret"></span>
	</a>
	<div class="dropdown-menu dropdown-menu-left" aria-labelledby="navbarDropdown">
        <a class="dropdown-item" href="{{ route('information.libros.lista') }}">{{ __("Individual") }}</a>
        <a class="dropdown-item" href="{{ route('information.libros.codes') }}">{{ __("Códigos") }}</a>
        <a class="dropdown-item" href="{{ route('codes.licencias_demos') }}">{{ __("Licencias / Demos") }}</a>
    </div>
</li>
<li>
	<a class="nav-link" href="{{ route('information.promotions.lista') }}">{{ __("Promociones") }}</a>
</li>
<li>
	<a class="nav-link" href="{{ route('information.donations.lista') }}">{{ __("Donaciones") }}</a>
</li>
@include('partials.navigations.logged')