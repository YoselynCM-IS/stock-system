<!doctype html>
<html>
    <head>
        <title>Reporte</title>
    </head>
    <body>
        <div>
            @include('download.partials.excel.fechas', ['fecha' => $fecha, 'inicio' => $inicio, 'final' => $final])
            @if($estado === 'cancelado')
                <h4>Remisiones CANCELADAS</h4>
            @endif
            @if($estado === 'no_entregado')
                <h4>Remisiones NO ENTREGADAS</h4>
            @endif
            @if($estado === 'entregado')
                <h4>Remisiones ENTREGADAS</h4>
            @endif
            @if($estado === 'pagado')
                <h4>Remisiones PAGADAS</h4>
            @endif
            <br>
            @include('download.partials.excel.table', ['remisiones' => $remisiones, 'totales' => $totales])
        </div>
    </body>
</html>
