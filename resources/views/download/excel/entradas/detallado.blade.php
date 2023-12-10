@foreach($entradas as $entrada)
    <table>
        <tr>
            <th><b>Folio</b></th>
            <th><b>Fecha</b></th>
            <th><b>Editorial</b></th>
            <th><b>Unidades</b></th>
            <th><b>Total</b></th>
            <th><b>Pagos</b></th>
            <th><b>Devolución</b></th>
            <th><b>Pendiente</b></th>
        </tr>
        <tr>
            <td>{{ $entrada->folio }}</td> 
            <td>{{ $entrada->created_at->format('Y-m-d') }}</td>
            <td>{{ $entrada->editorial }}</td>
            <td>{{ $entrada->unidades }}</td>
            <td>{{ $entrada->total }}</td>
            <td>{{ $entrada->total_pagos }}</td>
            <td>{{ $entrada->total_devolucion }}</td>
            <td>{{ $entrada->total - $entrada->total_pagos }}</td>
        </tr>
    </table>
    <table>
        <tr>
            <th><b>ISBN</b></th>
            <th><b>Libro</b></th>
            <th><b>Costo unitario</b></th>
            <th><b>Unidades</b></th>
            <th><b>Subtotal</b></th>
        </tr>
        @foreach($entrada->registros as $registro)
            <tr>
                <td>{{ $registro->libro->ISBN }}</td>
                <td>{{ $registro->libro->titulo }}</td>
                <td>{{ $registro->costo_unitario }}</td>
                <td>{{ $registro->unidades }}</td>
                <td>{{ $registro->total }}</td>
            </tr>
        @endforeach 
    </table><br>
@endforeach  