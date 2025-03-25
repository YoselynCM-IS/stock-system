@extends('layouts.app')

@section('content')
    <editar-entradas-component :role_id="{{auth()->user()->role_id}}" 
        :user_id="{{auth()->user()->id}}">
    </editar-entradas-component>
@endsection