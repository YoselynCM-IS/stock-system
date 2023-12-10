<table>
    <tr>
        <th><b>N.</b></th>
        <th><b>EDITORIAL</b></th>
        <th><b>ISBN</b></th>
        <th><b>LIBRO</b></th>
        <th><b>EXISTENCIA</b></th>
        <th><b>ENTRADAS</b></th>
        <th><b>DEVOLUCIÓN (REMISIONES)</b></th>
        <th><b>DEVOLUCIÓN (SALIDAS)</b></th>
        <th><b>DEVOLUCIÓN (PROMOCIONES)</b></th>
        <th><b>SALIDAS (QUERÉTARO)</b></th>
        <th><b>DEVOLUCIÓN (ENTRADAS)</b></th>
        <th><b>REMISIONES</b></th>
        <th><b>NOTAS</b></th>
        <th><b>DONACIONES</b></th>
        <th><b>PROMOCIONES</b></th>
        <th><b>DEFECTUOSOS</b></th>
    </tr>
    @foreach($movimientos as $movimiento)
        <tr>
            <td>{{ $movimiento['id'] }}</td>
            <td>{{ $movimiento['editorial'] }}</td>
            <td>{{ $movimiento['ISBN'] }}</td>
            <td>{{ $movimiento['libro'] }}</td>
            <td>{{ $movimiento['existencia'] }}</td>
            <td>{{ $movimiento['entradas'] }}</td>
            <td>{{ $movimiento['devoluciones'] }}</td>
            <td>{{ $movimiento['saldevoluciones'] }}</td>
            <td>{{ $movimiento['prodevoluciones'] }}</td>
            <td>{{ $movimiento['salidas'] }}</td>
            <td>{{ $movimiento['entdevoluciones'] }}</td>
            <td>{{ $movimiento['remisiones'] }}</td>
            <td>{{ $movimiento['notas'] }}</td>
            <td>{{ $movimiento['donaciones'] }}</td>
            <td>{{ $movimiento['promociones'] }}</td>
            <td>{{ $movimiento['defectuosos'] }}</td>
        </tr>
    @endforeach
</table>