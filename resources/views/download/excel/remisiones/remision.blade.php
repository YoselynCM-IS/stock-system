<!doctype html>
<html>
    <head>
        <title>Remisión</title>
    </head>
    <body>
        <div>
            <table>
                <!-- ROW 1 -->
                <tr>
                    <th height="15" width="0"></th> <!-- COLUMN A -->
                    <th width="10.6"></th> <!-- COLUMN B -->
                    <th width="17.4"></th> <!-- COLUMN C -->
                    <th width="13"></th> <!-- COLUMN D -->
                    <th width="16.7"></th> <!-- COLUMN E -->
                    <th width="13.8"></th> <!-- COLUMN F -->
                    <th width="12.1"></th> <!-- COLUMN G -->
                    <th width="15.45"></th> <!-- COLUMN H -->
                </tr>
                <!-- ROW 2 -->
                <tr><td></td></tr>
                <!-- ROW 3 -->
                <tr><td></td></tr>
                <!-- ROW 4 -->
                <tr>
                    <td height="15"></td>
                    <td></td>
                    <td colspan="2" style="font-size:9; font-family: Book Antiqua; text-align: center;">
                        <!-- COLUMNS C AND D MERGE TELEFONO -->
                    </td>
                    <td style="font-size:9; font-family: Book Antiqua;">
                        @if(env('APP_NAME') == 'MAJESTIC EDUCATION')
                            correo: admon.majestic.education@gmail.com
                        @else 
                            correo: contacto.omegabook@gmail.com
                        @endif
                    </td>
                    <td></td><td></td>
                    <td style="font-size:11; text-align: right;">
                        {{ $fecha->format('d') }} / {{ $fecha->format('m') }} / {{ $fecha->format('Y') }}
                    </td>
                </tr>
                <!-- ROW 5 -->
                <tr><td></td></tr>
                <!-- ROW 6 -->
                <tr>
                    <td height="22.5"></td>
                </tr>
                <!-- ROW 7 -->
                <tr>
                    <td height="14.2"></td>
                    <td style="font-size:12;"><b>CLIENTE:</b></td>
                    <td></td><td></td>
                    <td style="font-size:9;"></td>
                    <td></td>
                    <td style="font-size:9;"></td>
                </tr>
                <!-- ROW 8 -->
                <tr>
                    <td></td>
                    <td style="font-size:12;"><b>{{ $remision->cliente->name }}</b></td>
                    <td></td><td></td>
                    <td style="font-size:9; text-align: center;">
                        CREDITO {{ strtoupper($remision->cliente->condiciones_pago) }}
                    </td>
                    <td></td>
                    <td rowspan="4" colspan="2" style="text-align: justify; vertical-align: top; font-size:10;">{{ $remision->cliente->direccion }}</td>
                </tr>
                <!-- ROW 9 -->
                <tr>
                    <td height="14.2"></td>
                    <td style="font-size:10;">
                        @if(env('APP_NAME') == 'MAJESTIC EDUCATION' && $remision->cliente_id == 304)
                            {{ $remision->destino }}
                        @endif
                    </td>
                </tr>
                <!-- ROW 10 -->
                <tr>
                    <td height="14.2"></td><td></td><td></td><td></td>
                    <td style="font-size:9; text-align: center;">{{ $remision->cliente->contacto }}</td>
                </tr>
                <!-- ROW 11 -->
                <tr>
                    <td height="14.2"></td><td></td><td></td><td></td>
                    <td style="font-size:9;">{{ $remision->cliente->telefono }}</td>
                </tr>
                <!-- ROW 12 -->
                <tr><td height="19.5"></td></tr>
                <!-- ROW 13 -->
                <tr><td height="15"></td></tr>
                <!-- ROW 14 -->
                <tr><td height="15.7"></td></tr>
                <!-- ROW 15 -->
                <tr><td height="15.7"></td></tr>
                <!-- ROW 16 -->
                <tr><td height="15.7"></td></tr>
                <!-- ROW 17 -->
                <tr><td height="15.7"></td></tr>
                <!-- ROW 18 -->
                @foreach($datos as $dato)
                    <tr>
                        <td></td>
                        <td style="font-size:10; text-align: right;">{{ $dato['ISBN'] }}</td>
                        <td style="font-size:10;" colspan="3">{{ $dato['titulo'] }}</td>
                        <td style="font-size:10; text-align: center;">{{ number_format($dato['unidades']) }}</td>
                        <td style="font-size:10; text-align: center;">${{ number_format($dato['costo_unitario'], 2) }}</td>
                        <td style="font-size:10; text-align: center;">${{ number_format($dato['total'], 2) }}</td>
                    </tr>
                @endforeach
                @for ($i = 0; $i < $maximo; $i++)
                    <tr><td height="15.7"></td></tr>
                @endfor
                <tr>
                    <td height="15"></td><td></td><td></td><td></td>
                    <td colspan="3" style="font-size:10;">{{ strtoupper($total_letras) }} PESOS 00/100 MN</td>
                </tr>
                <tr>
                    <td height="15"></td><td></td><td></td><td></td><td></td><td></td>
                    <td style="font-size:11; text-align: center;"><b>TOTAL</b></td>
                    <td style="font-size:11;"><b>${{ number_format($remision->total, 2) }}</b></td>
                </tr>
            </table>
        </div>
    </body>
</html>
