@extends('layouts.app')

@section('content')
    <add-pack-component :role_id="{{auth()->user()->role_id}}"></add-pack-component>
@endsection