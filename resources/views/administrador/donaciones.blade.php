@extends('layouts.app')

@section('content')
    <donaciones-component :role_id="{{auth()->user()->role_id}}"></donaciones-component>
@endsection