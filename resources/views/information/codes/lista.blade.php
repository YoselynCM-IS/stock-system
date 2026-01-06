@extends('layouts.app')

@section('content')
    <codes-licencias-demos-component :role_id="{{auth()->user()->role_id}}"></codes-licencias-demos-component>
@endsection