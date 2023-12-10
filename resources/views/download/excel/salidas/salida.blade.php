<table>
    <tr>
        <td><b>Folio:</b></td>
        <td>{{ $salida->folio }}</td>
        <td></td>
        <td><b>Fecha:</b></td>
        <td>{{ $salida->created_at }}</td>
    </tr>
    <tr></tr>
    <tr>
        <th><b>ISBN</b></th>
        <th><b>Libro</b></th> 
        <th><b>Unidades</b></th>
    </tr>
    @foreach($salida->sregistros as $sregistro)
        <tr>
            <td>{{ $sregistro->libro->ISBN }}</td> 
            <td>{{ $sregistro->libro->titulo }}</td>
            <td>{{ number_format($sregistro->unidades) }}</td>
        </tr>
    @endforeach 
    <tr>
        <td></td>
        <td><b>TOTAL</b></td>
        <td><b>{{ number_format($salida->unidades) }}</b></td>
    </tr>
</table>