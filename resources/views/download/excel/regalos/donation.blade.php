<table>
    <tr>
        <td><b>Fecha:</b></td>
        <td>{{ $regalo->created_at }}</td>
    </tr>
    <tr>
        <td><b>Plantel:</b></td>
        <td>{{ $regalo->plantel }}</td>
        <td></td>
        <td><b>Descripción:</b></td>
        <td>{{ $regalo->descripcion }}</td>
    </tr>
    <tr></tr>
    <tr>
        <th><b>ISBN</b></th>
        <th><b>Libro</b></th> 
        <th><b>Unidades</b></th>
    </tr>
    @foreach($regalo->donaciones as $donacion)
        <tr>
            <td>{{ $donacion->libro->ISBN }}</td> 
            <td>{{ $donacion->libro->titulo }}</td>
            <td>{{ number_format($donacion->unidades) }}</td>
        </tr>
    @endforeach 
    <tr>
        <td></td>
        <td><b>TOTAL</b></td>
        <td><b>{{ number_format($regalo->unidades) }}</b></td>
    </tr>
</table>