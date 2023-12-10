@extends('layouts.app')

@section('content')
    <entrada-salida-component :role_id="{{auth()->user()->role_id}}"></entrada-salida-component>
@endsection