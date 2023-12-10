@extends('layouts.app')

@section('content')
    <promociones-component :role_id="{{auth()->user()->role_id}}"></promociones-component>
@endsection