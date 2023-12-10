@extends('layouts.app')

@section('content')
    <clientes-component :role_id="{{auth()->user()->role_id}}"></clientes-component>
@endsection