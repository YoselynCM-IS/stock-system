@extends('layouts.app-simple')

@section('content')
    <add-pago-corte-component  
        :corte="{{$cctotale}}" 
        :role_id="{{auth()->user()->role_id}}"
        :remdepositos="{{$remdepositos}}">
    </add-pago-corte-component>
@endsection