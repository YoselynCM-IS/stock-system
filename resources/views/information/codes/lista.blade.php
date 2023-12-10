@extends('layouts.app')

@section('content')
    <b-tabs content-class="mt-3" justified>
        <b-tab title="Licencias" active>
            @include('information.codes.partials.tabla', ['libros' => $profesor, 'diferencia' => false])
        </b-tab>
        <b-tab title="Demos">
            @include('information.codes.partials.tabla', ['libros' => $demo, 'diferencia' => false])
        </b-tab>
    </b-tabs>
@endsection