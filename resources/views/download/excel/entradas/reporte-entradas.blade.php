<!doctype html>
<html>
    <head>
        <title>Entrada</title>
    </head>
    <body>
        <div>
            <div>
                @include('download.partials.excel.fechas', ['fecha' => $fecha, 'inicio' => $inicio, 'final' => $final])
                @if($editorial != 'TODAS')
                    <label><b>Editorial: </b> {{ $editorial }}</label><br>
                @endif
                <br>
                @if($tipo === 'general')
                    @include('download.excel.entradas.general', ['entradas' => $entradas, 'totales' => $totales])
                @else 
                    @include('download.excel.entradas.detallado', ['entradas' => $entradas, 'totales' => $totales])
                @endif
            </div>
        </div>
    </body>
</html>
