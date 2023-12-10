@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-sm">
            {{ $registros->links() }}
        </div>
        <div class="col-sm">
            <form action="{{ route('information.reportes.ventas.by_fecha') }}" method="GET">
                <div class="form-row">
                    <div class="col-sm-4">
                        <input type="date" class="form-control" name="fi" value="{{$fi}}">
                    </div>
                    <div class="col-sm-4">
                        <input type="date" class="form-control" name="ff" value="{{$ff}}">
                    </div>
                    <div class="col-sm-4">
                        <b-button type="submit" pill block variant="info">Buscar</b-button>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-sm-2">
            <b-button pill block variant="dark" 
                href="{{ route('information.reportes.ventas.download', [$fi, $ff]) }}">Descargar</b-button>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th scope="col">N.</th>
                    <th scope="col">Editorial</th>
                    <th scope="col">Tipo (Libro)</th>
                    <th scope="col">ISBN</th>
                    <th scope="col">Titulo</th>
                    <th scope="col">Costo Unitario</th>
                    <th scope="col">Unidades</th>
                    <th scope="col">Total</th>
                    <th scope="col">Remisión</th>
                    <th scope="col">Tipo (Cliente)</th>
                    <th scope="col">Cliente</th>
                    <th scope="col">Creado el</th>
                    <!-- <th scope="col">cliente_id</th> -->
                    <!-- <th scope="col">Total</th>
                    <th scope="col">Devolución</th>
                    <th scope="col">Pagar</th>
                    <th scope="col">Pagos</th> -->
                    <!-- <th scope="col">created_at</th> -->
                    <!-- <th scope="col">libro_id</th> -->
                </tr>
            </thead>
            <tbody>
                @foreach($registros as $registro)
                    <tr>
                        <th scope="row">{{$loop->index + 1}}</th>
                        <td>{{$registro->libro_editorial}}</td> 
                        <td>{{$registro->libro_type}}</td> 
                        <td>{{$registro->libro_isbn}}</td> 
                        <td>{{$registro->libro_titulo}}</td> 
                        <td>{{$registro->dato_costo}}</td> 
                        <td>{{$registro->dato_unidades}}</td> 
                        <td>{{$registro->dato_total}}</td>
                        <td>{{$registro->remisione_id}}</td> 
                        <td>{{$registro->cliente_tipo}}</td> 
                        <td>{{$registro->cliente_name}}</td> 
                        <td>{{$registro->rem_fecha}}</td> 
                        <!-- <td>{{$registro->cliente_id}}</td>  -->
                        <!-- <td>{{$registro->rem_total}}</td> 
                        <td>{{$registro->rem_devolucion}}</td> 
                        <td>{{$registro->rem_pagar}}</td> 
                        <td>{{$registro->rem_pagos}}</td>  -->
                        <!-- <td>{{$registro->rem_created}}</td> 
                        <td>{{$registro->libro_id}}</td>  -->
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection