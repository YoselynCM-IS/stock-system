<table>
    <tr>
        <th><b>Folio</b></th>
        <th><b>Cliente</b></th>
        <th><b>Fecha de creación</b></th>
        <th><b>Salida</b></th>
        <th><b>Pagos</b></th>
        <th><b>Devolución</b></th>
        <th><b>Pagar</b></th>
    </tr>
    @foreach($notes as $note)
        <tr>
            <td>{{ $note->folio }}</td> 
            <td>{{ $note->cliente }}</td>
            <td>{{ $note->created_at->format('Y-m-d') }}</td>
            <td>{{ $note->total_salida }}</td>
            <td>{{ $note->pagos }}</td>
            <td>{{ $note->total_devolucion }}</td>
            <td>{{ $note->total_pagar }}</td>
        </tr>
    @endforeach  
    <tr>
        <td></td><td></td><td></td>
        <td><b>{{ $totales['total_salida'] }}</b></td>
        <td><b>{{ $totales['total_pagos'] }}</b></td>
        <td><b>{{ $totales['total_devolucion'] }}</b></td>
        <td><b>{{ $totales['total_pagar'] }}</b></td>
    </tr>
</table>