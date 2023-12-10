@extends('layouts.app')

@section('content')
    <codes-component :role_id="{{auth()->user()->role_id}}"></codes-component>
@endsection