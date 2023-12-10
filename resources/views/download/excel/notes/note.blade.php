<table>
    <tr>
        <td><b>Folio:</b></td>
        <td>{{ $note->folio }}</td>
        <td></td>
        <td><b>Fecha:</b></td>
        <td>{{ $note->created_at }}</td>
    </tr>
    <tr>
        <td><b>Cliente:</b></td>
        <td>{{ $note->cliente }}</td>
    </tr>
    <tr></tr>
    <tr>
        <th><b>ISBN</b></th>
        <th><b>Libro</b></th> 
        <th><b>Unidades</b></th>
        <th><b>Costo unitario</b></th>
        <th><b>Total</b></th>
    </tr>
    @foreach($note->registers as $register)
        <tr>
            <td>{{ $register->libro->ISBN }}</td> 
            <td>{{ $register->libro->titulo }}</td>
            <td>{{ number_format($register->unidades) }}</td>
            <td>${{ number_format($register->costo_unitario) }}</td>
            <td>${{ number_format($register->total) }}</td>
        </tr>
    @endforeach 
    <tr>
        <td></td><td></td><td></td>
        <td><b>TOTAL</b></td>
        <td><b>${{ number_format($note->total) }}</b></td>
    </tr>
</table>