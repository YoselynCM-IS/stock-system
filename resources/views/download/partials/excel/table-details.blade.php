@foreach($remisiones as $remision)
    <table>
        <tr>
            <th></th>
            <th><b>FOLIO</b></th>
            <th><b>FECHA</b></th> 
            <th><b>CLIENTE</b></th>
            <th><b>SALIDA</b></th>
            <th><b>PAGOS</b></th>
            <th><b>DEVOLUCIÓN</b></th>
            <th><b>PAGAR</b></th>
        </tr>
        <tr>
            <td>
                @if($remision->estado === 'Cancelado')
                    <b>{{ strtoupper($remision->estado) }}</b> 
                @endif
            </td>
            <td>{{ $remision->id }}</td>
            <td>{{ $remision->fecha_creacion }}</td>
            <td>{{ $remision->cliente->name }}</td>
            <td>{{ $remision->total }}</td>
            <td>{{ $remision->pagos }}</td>
            <td>{{ $remision->total_devolucion }}</td>
            <td>{{ $remision->total_pagar }}</td>
        </tr>
    </table>
    <table>
        <tr>
            <th><b>ISBN</b></th>
            <th><b>TITULO</b></th> 
            <th><b>COSTO UNITARIO</b></th>
            <th><b>UNIDADES</b></th>
            <th><b>SUBTOTAL</b></th>
        </tr>
        @foreach($remision['datos'] as $dato)
        <tr>
            <td>{{$dato['libro']['ISBN']}}</td>
            <td>{{$dato['libro']['titulo']}}</td>
            <td>{{$dato['costo_unitario']}}</td>
            <td>{{$dato['unidades']}}</td>
            <td>{{$dato['total']}}</td>
        </tr>
        @endforeach
    </table><br>
@endforeach