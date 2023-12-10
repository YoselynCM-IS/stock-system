<!doctype html>
<html>
    <head>
        
    </head>
    <body>
        <div>
            <table>
                <tr>
                    <th><b>ISBN</b></th>
                    <th><b>LIBRO</b></th>
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
                </tr>
                @foreach($movimientos as $movimiento)
                    <tr>
                        <td>{{ $movimiento['isbn'] }}</td>
                        <td>{{ $movimiento['libro'] }}</td>
                        <td>{{ $movimiento['entradas'] }}</td>
                        <td>{{ $movimiento['devoluciones'] }}</td>
                        <td>{{ $movimiento['saldevoluciones'] }}</td>
                        <td>{{ $movimiento['prodevoluciones'] }}</td>
                        <td>{{ $movimiento['salidas'] }}</td>
                        <td>{{ $movimiento['entdevoluciones'] }}</td>
                        <td>{{ $movimiento['remisiones'] }}</td>
                        <td>{{ $movimiento['notas'] }}</td>
                        <td>{{ $movimiento['promociones'] }}</td>
                        <td>{{ $movimiento['donaciones'] }}</td>
                    </tr>
                    <tr>
                        <td></td><td></td>
                        @include('download.excel.movimientos.partials.cliente-unidades', ['elementos' => $movimiento['detalles']['entradas'], 'cliente' => false, 'etiqueta' => 'FOLIO'])
                        @include('download.excel.movimientos.partials.cliente-unidades', ['elementos' => $movimiento['detalles']['devoluciones'], 'cliente' => true, 'etiqueta' => 'FOLIO'])
                        @include('download.excel.movimientos.partials.cliente-unidades', ['elementos' => $movimiento['detalles']['saldevoluciones'], 'cliente' => false, 'etiqueta' => 'FOLIO'])
                        @include('download.excel.movimientos.partials.cliente-unidades', ['elementos' => $movimiento['detalles']['prodevoluciones'], 'cliente' => false, 'etiqueta' => 'FOLIO'])
                        @include('download.excel.movimientos.partials.cliente-unidades', ['elementos' => $movimiento['detalles']['salidas'], 'cliente' => false, 'etiqueta' => 'FOLIO'])
                        @include('download.excel.movimientos.partials.cliente-unidades', ['elementos' => $movimiento['detalles']['entdevoluciones'], 'cliente' => false, 'etiqueta' => 'FOLIO'])
                        @include('download.excel.movimientos.partials.cliente-unidades', ['elementos' => $movimiento['detalles']['remisiones'], 'cliente' => true, 'etiqueta' => 'FOLIO'])
                        @include('download.excel.movimientos.partials.cliente-unidades', ['elementos' => $movimiento['detalles']['notas'], 'cliente' => false, 'etiqueta' => 'FOLIO'])
                        @include('download.excel.movimientos.partials.cliente-unidades', ['elementos' => $movimiento['detalles']['promociones'], 'cliente' => false, 'etiqueta' => 'FOLIO'])
                        @include('download.excel.movimientos.partials.cliente-unidades', ['elementos' => $movimiento['detalles']['donaciones'], 'cliente' => false, 'etiqueta' => 'PLANTEL'])
                    </tr>
                @endforeach
            </table>
        </div>
    </body>
</html>