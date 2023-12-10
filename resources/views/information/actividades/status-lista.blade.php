@extends('layouts.app')

@section('content')
    <status-actividades-component 
        :role_id="{{ auth()->user()->role_id }}"
        status="{{$status}}"></status-actividades-component>
@endsection