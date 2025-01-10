@extends('layouts.app-simple')

@section('content')
    <add-edit-entrada :agregar="{{$agregar}}"></add-edit-entrada>
@endsection