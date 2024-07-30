@extends('layouts.app-simple')

@section('content')
    <new-edit-pedido-component :pedido="{{$pedido}}"></new-edit-pedido-component>
@endsection