@extends('layouts.app')

@section('content')
    <libros-component :role_id="{{auth()->user()->role_id}}" :editoriales="{{$editoriales}}" :types="{{$types}}"></libros-component>
@endsection