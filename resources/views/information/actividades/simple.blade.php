@extends('layouts.app')

@section('content')
    <actividades-component :role_id="{{ auth()->user()->role_id }}"></actividades-component>
@endsection