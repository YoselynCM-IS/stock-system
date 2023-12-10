<!doctype html>
<html>
    <head>
        <title>Reporte</title>
    </head>
    <body>
        <div>
            @include('download.partials.excel.fechas', ['fecha' => $fecha, 'inicio' => $inicio, 'final' => $final])
            @if($cliente != null)
                <h4><b>Cliente:</b> {{ $cliente->name }}</h4>
            @endif
            @if($editorial != null)
                <h4><b>Editorial:</b> {{ $editorial }}</h4>
            @endif
            <br>
            <table>
                <tr>
                    <th><b>Libro</b></th>
                    <th><b>Unidades Vendidas</b></th>
                    <th><b>Subtotal</b></th>
                    <th><b>Unidades pendientes</b></th>
                    <th><b>Subtotal</b></th>
                </tr>
                @foreach($datos as $dato)
                    <tr>
                        <td>{{ $dato->libro }}</td>
                        <td>{{ $dato->unidades_vendido }}</td>
                        <td>{{ $dato->total_vendido }}</td>
                        <td>{{ $dato->unidades_pendiente }}</td>
                        <td>{{ $dato->total_pendiente }}</td>
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