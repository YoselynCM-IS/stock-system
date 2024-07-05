@extends('layouts.app-simple')

@section('content')
    <remision-component :role_id="{{auth()->user()->role_id}}"
        :editar="{{$editar}}"
        :datoremision="{{$remision}}"></remision-component>
@endsection