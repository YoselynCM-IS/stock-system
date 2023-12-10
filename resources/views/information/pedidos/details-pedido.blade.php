@extends('layouts.app-simple')

@section('content')
    <details-pedido-component :pedido="{{$pedido}}" :role_id="{{auth()->user()->role_id}}"></details-pedido-component>
@endsection