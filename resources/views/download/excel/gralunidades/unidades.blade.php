<!doctype html>
<html>
    <head>
        <title>Reporte</title>
    </head>
    <body>
        <div>
            <table>
                @foreach($registros as $registro)
                    <tr>
                        <td colspan="4"><b><i>{{ $registro['gral']['cliente'] }}</i></b></td>
                    </tr>
                    <tr>
                        <td><b>Libro</b></td>
                        <td><b>Vendidas</b></td>
                        <td><b>Salida</b></td>
                        <td><b>Devoluciones</b></td>
                    </tr>
                    @foreach($registro['libros'] as $libro)
                        <tr>
                            <td>{{ $libro['libro'] }}</td>
                            <td>{{ $libro['unidades_vendidas'] }}</td>
                            <td>{{ $libro['unidades_remisiones'] }}</td>
                            <td>{{ $libro['unidades_devoluciones'] }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td align="right"><b>TOTAL</b></td>
                        <td align="right"><b>{{ $registro['gral']['unidades_vendidas'] }}</b></td>
                        <td align="right"><b>{{ $registro['gral']['unidades_remisiones'] }}</b></td>
                        <td align="right"><b>{{ $registro['gral']['unidades_devoluciones'] }}</b></td>
                    </tr>
                    <tr></tr>
                @endforeach
            </table>
        </div>
    </body>
</html>
