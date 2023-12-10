<!doctype html>
<html>
    <head>
        <title>Reporte</title>
    </head>
    <body>
        <div>
            @include('download.partials.excel.fechas', ['fecha' => $fecha, 'inicio' => $inicio, 'final' => $final])
            <br>
            <table>
                <tr>
                    <th><b>LIBRO</b></th>
                    <th><b>UNIDADES</b></th>
                    <th><b>TOTAL</b></th>
                </tr>
                @foreach($movimientos as $movimiento)
                    <tr>
                        <td>{{ $movimiento->libro }}</td>
                        <td>{{ $movimiento->unidades }}</td>
                        <td>{{ $movimiento->total }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    </body>
</html>
