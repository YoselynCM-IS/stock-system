@extends('layouts.app-simple')

@section('content')
    <new-edit-pedido-component :tipo="{{$tipo}}" :pedido="{{$pedido}}"></new-edit-pedido-component>
@endsection