@extends('layouts.app-simple')

@section('content')
    <register-devolucion-component :remision="{{$remision}}"></register-devolucion-component>
@endsection