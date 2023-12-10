@extends('layouts.app-simple')

@section('content')
    <details-order-component :pedido="{{$order}}" :role_id="{{auth()->user()->role_id}}"></details-order-component>
@endsection