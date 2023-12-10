<!doctype html>
<html>
    <head>
        <title>Reporte</title>
    </head>
    <body>
        <div>
            @if($editorial !== 'TODO')
                <h4><b>{{ $editorial }}</b></h4>
            @endif
            @if($fecha !== null)
                <h4><b>{{ $fecha }}</b></h4>
            @endif
            <table>
                <tr>
                    <th><b>LIBRO</b></th>
                    <th><b>ENTRADAS</b></th>
                    <th><b>DEVOLUCIÓN (REMISIONES)</b></th>
                    <th><b>TOTAL (ENTRADAS)</b></th>
                    <th><b>DEVOLUCIÓN (ENTRADAS)</b></th>
                    <th><b>REMISIONES</b></th>
                    <th><b>NOTAS</b></th>
                    <th><b>TOTAL (SALIDA)</b></th>
                    <th><b>TOTAL</b></th>
                </tr>
                @foreach($movimientos as $movimiento)
                    <tr>
                        <td>{{ $movimiento['titulo'] }}</td>
                        <td>${{ $movimiento['entradas'] }}</td>
                        <td>${{ $movimiento['devoluciones'] }}</td>
                        <td>${{ $movimiento['total_entrada'] }}</td>
                        <td>${{ $movimiento['entdevoluciones'] }}</td>
                        <td>${{ $movimiento['remisiones'] }}</td>
                        <td>${{ $movimiento['notas'] }}</td>
                        <td>${{ $movimiento['total_salida'] }}</td>
                        <td>${{ $movimiento['total'] }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    </body>
</html>
