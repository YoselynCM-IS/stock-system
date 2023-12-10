<!doctype html>
<html>
    <head>
        <title>Notas</title>
    </head>
    <body>
        <div>
            <div>
                @include('download.partials.excel.fechas', ['fecha' => $fecha, 'inicio' => $inicio, 'final' => $final])
                <br>
                @if($tipo === 'general')
                    @include('download.excel.regalos.general', ['regalos' => $regalos, 'total_unidades' => $total_unidades])
                @endif
                @if($tipo === 'detallado')
                    @include('download.excel.regalos.detallado', ['regalos' => $regalos])
                @endif
            </div>
        </div>
    </body>
</html>