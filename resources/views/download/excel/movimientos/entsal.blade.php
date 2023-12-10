<table>
    <tr>
        <th><b>Libro</b></th>
        <th><b>Entradas</b></th>
        <th><b>Devoluciones (Remisiones)</b></th>
        <th><b>Salidas (Querétaro)</b></th>
        <th><b>Devoluciones (Entradas)</b></th>
        <th><b>Remisiones</b></th>
        <th><b>Notas</b></th>
        <th><b>Promociones</b></th>
        <th><b>Donaciones</b></th>	
    </tr>
    @foreach($movimientos as $movimiento)
        <tr>
            <td>{{ $movimiento['libro'] }}</td>
            <td>{{ $movimiento['entradas'] }}</td>
            <td>{{ $movimiento['devoluciones'] }}</td>
            <td>{{ $movimiento['salidas'] }}</td>
            <td>{{ $movimiento['entdevoluciones'] }}</td>
            <td>{{ $movimiento['remisiones'] }}</td>
            <td>{{ $movimiento['notas'] }}</td>
            <td>{{ $movimiento['promociones'] }}</td>
            <td>{{ $movimiento['donaciones'] }}</td>
        </tr>
    @endforeach
</table>