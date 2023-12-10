@extends('layouts.app')

@section('content')
    <crm-inicio-component :role_id="{{ auth()->user()->role_id }}"></crm-inicio-component>
@endsection