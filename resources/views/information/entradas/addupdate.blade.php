@extends('layouts.app-simple')

@section('content')
    <add-edit-entrada :agregar="{{$agregar}}" :entrada="{{$entrada}}"
        :user_id="{{auth()->user()->id}}"></add-edit-entrada>
@endsection