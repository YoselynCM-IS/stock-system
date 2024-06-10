@extends('layouts.app-simple')

@section('content')
    <update-entrada-component :entrada="{{$entrada}}"></update-entrada-component>
@endsection