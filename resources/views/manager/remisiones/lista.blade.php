@extends('layouts.app')

@section('content')
    <listado-component 
        :role_id="{{auth()->user()->role_id}}" 
        :listresponsables="{{$responsables}}"></listado-component>
@endsection