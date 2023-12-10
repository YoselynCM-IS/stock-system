@extends('layouts.app')

@section('content')
    <lista-cortes-component :role_id="{{auth()->user()->role_id}}"></lista-cortes-component>
@endsection