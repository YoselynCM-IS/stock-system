@extends('layouts.app-simple')

@section('content')
    <details-pagos-component :role_id="{{auth()->user()->role_id}}"
        :clienteid="{{$cliente_id}}"></details-pagos-component>
@endsection