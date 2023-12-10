<!doctype html>
<html>
    <head>
        <title>Reporte</title>
    </head>
    <body>
        <div>
            @include('download.partials.excel.fechas', ['fecha' => $fecha, 'inicio' => $inicio, 'final' => $final])
            @foreach($vendidos as $vendido)
                <h4><b>Cliente</b>: {{ $vendido['cliente'] }}</h4>
                <table>
                    <tr>
                        <th><b>Libro</b></th>
                        <th><b>Unidades Vendidas</b></th>
                        <th><b>Subtotal</b></th>
                        <th><b>Unidades pendientes</b></th>
                        <th><b>Subtotal</b></th>
                    </tr>
                    @foreach($vendido['registros'] as $registro)
                        <tr>
                            <td>{{ $registro->libro }}</td>
                            <td>{{ $registro->unidades_vendido }}</td>
                            <td>{{ $registro->total_vendido }}</td>
                            <td>{{ $registro->unidades_pendiente }}</td>
                            <td>{{ $registro->total_pendiente }}</td>
                        </tr>
                    @endforeach
                </table>
                <br>
            @endforeach
        </div>
    </body>
</html>