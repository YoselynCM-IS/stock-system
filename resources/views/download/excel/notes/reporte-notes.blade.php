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
                    @include('download.excel.notes.general', ['notes' => $notes, 'totales' => $totales])
                @endif
                @if($tipo === 'detallado')
                    @include('download.excel.notes.detallado', ['notes' => $notes])
                @endif
            </div>
        </div>
    </body>
</html>
