<table>
    <tr>
        <th><b>EDITORIAL</b></th>
        <th><b>TOTAL</b></th>
        <th><b>PAGOS</b></th>
        <th><b>DEVOLUCIÓN</b></th>
        <th><b>PAGAR</b></th>
    </tr>
    @foreach($editoriales as $editorial)
        <tr>
            <td>{{ $editorial->editorial }}</td>
            <td>${{ number_format($editorial->total, 2) }}</td>
            <td>${{ number_format($editorial->total_pagos, 2) }}</td>
            <td>${{ number_format($editorial->total_devolucion, 2) }}</td>
            <td>${{ number_format($editorial->total_pendiente, 2) }}</td>
        </tr>
    @endforeach  
    <tr>
        <td></td>
        <td><b>${{ number_format($totales['total'], 2) }}</b></td>
        <td><b>${{ number_format($totales['total_pagos'], 2) }}</b></td>
        <td><b>${{ number_format($totales['total_devolucion'], 2) }}</b></td>
        <td><b>${{ number_format($totales['total_pendiente'], 2) }}</b></td>
    </tr>
</table>