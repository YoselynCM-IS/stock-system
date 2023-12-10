<li>
	<a class="nav-link" href="{{ route('information.actividades.simple') }}">{{ __("Actividades") }}</a>
</li>
<user-notifications :user_id="{{auth()->user()->id}}" :noleidos="{{Auth::user()->unreadNotifications}}"></user-notifications>
@include('partials.navigations.logged')