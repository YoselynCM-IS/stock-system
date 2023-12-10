@extends('layouts.app')

@section('content')
    <pedido-school-component :role_id="{{auth()->user()->role_id}}"></pedido-school-component>
@endsection