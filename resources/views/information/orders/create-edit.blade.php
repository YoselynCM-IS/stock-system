@extends('layouts.app-simple')

@section('content')
    <new-edit-order-component :tipo="{{$tipo}}" :order="{{$order}}"></new-edit-order-component>
@endsection