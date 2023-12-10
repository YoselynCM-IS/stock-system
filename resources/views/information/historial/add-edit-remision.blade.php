@extends('layouts.app-simple')

@section('content')
    <add-edit-remision-component 
        :role_id="{{auth()->user()->role_id}}"
        :clientesall="{{$clientes}}" 
        :editar="{{$editar}}"
        :datoremision="{{$remision}}"
        :cortes="{{$cortes}}">
    </add-edit-remision-component>
@endsection