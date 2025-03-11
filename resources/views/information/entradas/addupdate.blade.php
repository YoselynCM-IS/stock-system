@extends('layouts.app-simple')

@section('content')
    <add-edit-entrada :agregar="{{$agregar}}" :entrada="{{$entrada}}"></add-edit-entrada>
@endsection