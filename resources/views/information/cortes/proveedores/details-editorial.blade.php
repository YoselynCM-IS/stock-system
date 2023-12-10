@extends('layouts.app-simple')

@section('content')
    <corte-editorial-component :role_id="{{auth()->user()->role_id}}"
        editorial="{{$editorial}}"></corte-editorial-component>
@endsection