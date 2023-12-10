<table>
    <tr>
        <th>FOLIO</th>
        <th>FECHA</th> 
        <th>CLIENTE</th>
        <th>SALIDA</th>
        <th>PAGOS</th>
        <th>DEVOLUCIÓN</th>
        <th>PAGAR</th>
    </tr>
    @foreach($remisiones as $remision)
        @if($remision->estado === 'Iniciado')
            <tr style="background-color: #dbdbdb;">
        @endif
        @if($remision->estado === 'Cancelado')
            <tr  style="background-color: #ffd6d6;">
        @endif
        @if($remision->estado !== 'Iniciado' && $remision->estado !== 'Cancelado')
            <tr>
        @endif
            <td style="width:5%" id="tdder">{{ $remision->id }}</td>
            <td style="width:15%" id="tdcent">{{ $remision->fecha_creacion }}</td>
            <td style="width:40%">{{ $remision->cliente->name }}</td>
            <td style="width:10%" id="tdder">${{ number_format($remision->total, 2) }}</td>
            <td style="width:10%" id="tdder">${{ number_format($remision->pagos, 2) }}</td>
            <td style="width:10%" id="tdder">${{ number_format($remision->total_devolucion, 2) }}</td>
            <td style="width:10%" id="tdder">${{ number_format($remision->total_pagar, 2) }}</td>
        </tr>
    @endforeach
    <tr>
        <td></td><td></td>
        <td id="tdder"><b>TOTAL</b></td>
        <td id="tdder"><b>${{ number_format($totales['total_salida'], 2) }}</b></td>
        <td id="tdder"><b>${{ number_format($totales['total_pagos'], 2) }}</b></td>
        <td id="tdder"><b>${{ number_format($totales['total_devolucion'], 2) }}</b></td>
        <td id="tdder"><b>${{ number_format($totales['total_pagar'], 2) }}</b></td>
    </tr>
</table>