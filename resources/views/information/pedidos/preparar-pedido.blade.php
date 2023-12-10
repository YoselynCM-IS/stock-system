@extends('layouts.app-simple')

@section('content')
    <preparar-pedido-component :pedido="{{$pedido}}"></preparar-pedido-component>
@endsection