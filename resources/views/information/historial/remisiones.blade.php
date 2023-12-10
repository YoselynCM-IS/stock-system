@extends('layouts.app')

@section('content')
    <remisiones-component :corte_id="{{$corte_id}}"></remisiones-component>
@endsection