<li>
	<a class="nav-link" href="{{ route('information.actividades.lista') }}">{{ __("Actividades") }}</a>
</li>
<li>
	<a class="nav-link" href="{{ route('information.pedidos.cliente') }}">{{ __("Pedidos") }}</a>
</li>
<li>
	<a class="nav-link" href="{{ route('information.clientes.lista') }}">{{ __("Clientes") }}</a>
</li>
<li>
	<a class="nav-link" href="{{ route('information.remisiones.lista') }}">{{ __("Remisiones") }}</a>
</li>
<li>
	<a class="nav-link" href="{{ route('information.promotions.lista') }}">{{ __("Promociones") }}</a>
</li>
<li>
	<a class="nav-link" href="{{ route('information.donations.lista') }}">{{ __("Donaciones") }}</a>
</li>
<li>
	<a class="nav-link" href="{{ route('libro.all_sistemas') }}">{{ __("Inventario") }}</a>
</li>
<user-notifications :user_id="{{auth()->user()->id}}" :noleidos="{{Auth::user()->unreadNotifications}}"></user-notifications>
@include('partials.navigations.logged')