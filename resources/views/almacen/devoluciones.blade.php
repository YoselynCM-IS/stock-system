@extends('layouts.app')

@section('content')
    <pagos-devoluciones-component :role_id="{{auth()->user()->role_id}}" :listresponsables="{{$responsables}}"></pagos-devoluciones-component>
@endsection