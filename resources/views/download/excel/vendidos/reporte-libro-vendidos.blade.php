<!doctype html>
<html>
    <head>
        <title>Reporte</title>
    </head>
    <body>
        <div>
            @include('download.partials.excel.fechas', ['fecha' => $fecha, 'inicio' => $inicio, 'final' => $final])
            <table>
                <tr>
                    <th><b>Cliente</b></th>
                    <th><b>Unidades Vendidas</b></th>
                    <th><b>Subtotal</b></th>
                    <th><b>Unidades pendientes</b></th>
                    <th><b>Subtotal</b></th>
                </tr>
                @foreach($vendidos as $vendido)
                    <tr>
                        <td>{{ $vendido->cliente }}</td>
                        <td>{{ $vendido->unidades_vendido }}</td>
                        <td>{{ $vendido->total_vendido }}</td>
                        <td>{{ $vendido->unidades_pendiente }}</td>
                        <td>{{ $vendido->total_pendiente }}</td>
                    </tr>
                @endforeach
                <tr>
                    <th></th>
                    <th><b>{{ $totales['unidades_vendido'] }}</b></th>
                    <th><b>{{ $totales['subtotal_vendidas'] }}</b></th>
                    <th><b>{{ $totales['unidades_pendiente'] }}</b></th>
                    <th><b>{{ $totales['subtotal_pendientes'] }}</b></th>
                </tr>
            </table>
        </div>
    </body>
</html>