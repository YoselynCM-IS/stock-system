@extends('layouts.app')

@section('content')
    <orders-component :role_id="{{auth()->user()->role_id}}"></orders-component>
@endsection