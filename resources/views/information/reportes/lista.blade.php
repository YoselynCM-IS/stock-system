@extends('layouts.app')

@section('content')
    <reportes-listado-component :role_id="{{auth()->user()->role_id}}"></reportes-listado-component>
@endsection