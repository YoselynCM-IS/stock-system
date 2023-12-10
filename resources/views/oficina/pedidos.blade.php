@extends('layouts.app')

@section('content')
    <pedidos-component :role_id="{{auth()->user()->role_id}}"></pedidos-component>
@endsection