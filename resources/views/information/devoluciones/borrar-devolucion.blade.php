@extends('layouts.app-simple')

@section('content')
    <del-devolucion-component :remision="{{$remision}}"></del-devolucion-component>
@endsection