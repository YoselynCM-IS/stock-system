@extends('layouts.app')

@section('content')
    <pagos-entradas-component :role_id="{{auth()->user()->role_id}}"
        :editoriales="{{$editoriales}}"></pagos-entradas-component>
@endsection