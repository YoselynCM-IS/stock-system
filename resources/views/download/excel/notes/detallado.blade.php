@foreach($notes as $note)
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
        <tr>
            <td>{{ $note->folio }}</td> 
            <td>{{ $note->cliente }}</td>
            <td>{{ $note->created_at->format('Y-m-d') }}</td>
            <td>{{ $note->total_salida }}</td>
            <td>{{ $note->pagos }}</td>
            <td>{{ $note->total_devolucion }}</td>
            <td>{{ $note->total_pagar }}</td>
        </tr>
    </table>
    <table>
        <tr>
            <th><b>ISBN</b></th>
            <th><b>Libro</b></th>
            <th><b>Costo unitario</b></th>
            <th><b>Unidades</b></th>
            <th><b>Subtotal</b></th>
            <th><b>U. vendidas</b></th>
            <th><b>U. devueltas</b></th>
            <th><b>U. pendientes</b></th>

        </tr>
        @foreach($note->registers as $register)
            <tr>
                <td>{{ $register->libro->ISBN }}</td>
                <td>{{ $register->libro->titulo }}</td>
                <td>{{ $register->costo_unitario }}</td>
                <td>{{ $register->unidades }}</td>
                <td>{{ $register->total }}</td>
                <td>{{ $register->unidades_pagado }}</td>
                <td>{{ $register->unidades_devuelto }}</td>
                <td>{{ $register->unidades_pendiente }}</td>
            </tr>
        @endforeach
    </table><br>
@endforeach