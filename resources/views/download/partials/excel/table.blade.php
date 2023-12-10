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
    @foreach($remisiones as $remision)
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
    @endforeach
    <tr>
        <td></td><td></td><td></td>
        <td><b>TOTAL</b></td>
        <td><b>{{ $totales['total_salida'] }}</b></td>
        <td><b>{{ $totales['total_pagos'] }}</b></td>
        <td><b>{{ $totales['total_devolucion'] }}</b></td>
        <td><b>{{ $totales['total_pagar'] }}</b></td>
    </tr>
</table>