@extends('layouts.app')

@section('content')
    <add-pack-component :role_id="{{auth()->user()->role_id}}"></add-pack-component>
    @include('information.codes.partials.tabla', ['libros' => $scratch, 'diferencia' => true])
@endsection