@extends('layouts.app')

@section('content')
    <salidas-lista-component :role_id="{{auth()->user()->role_id}}"></salidas-lista-component>
@endsection