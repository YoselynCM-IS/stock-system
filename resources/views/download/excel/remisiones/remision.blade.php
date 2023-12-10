<!doctype html>
<html>
    <head>
        <title>Remisión</title>
    </head>
    <body>
        <div>
            <table>
                <tr>
                    <th height="15" width="0"></th>
                    <th width="10.6"></th>
                    <th width="17.4"></th>
                    <th width="13"></th>
                    <th width="16.7"></th>
                    <th width="13.8"></th>
                    <th width="12.1"></th>
                    <th width="15.45"></th>
                </tr>
                <tr>
                    <td height="16.5"></td>
                </tr>
                <tr>
                    <td height="15"></td>
                    <td></td>
                    <td colspan="2" style="font-size:9; font-family: Book Antiqua; text-align: center;">
                        Tel. 55-5803-6415
                    </td>
                    <td style="font-size:9; font-family: Book Antiqua;">
                        @if(env('APP_NAME') == 'MAJESTIC EDUCATION')
                            correo: admon.majestic.education@gmail.com
                        @else 
                            correo: contacto.omegabook@gmail.com
                        @endif
                    </td><td></td>
                    <td colspan="2" style="font-size:11; text-align: right;">
                        {{ $fecha->format('d') }} / {{ $fecha->format('m') }} / {{ $fecha->format('Y') }}
                    </td>
                </tr>
                <tr>
                    <td height="22.5"></td>
                </tr>
                <tr>
                    <td height="14.2"></td>
                    <td style="font-size:12;"><b>CLIENTE:</b></td>
                    <td></td><td></td>
                    <td style="font-size:9;"><b>CONDICIONES DE PAGO: </b></td>
                    <td></td>
                    <td style="font-size:9;"><b>DIRECCION DE ENTREGA: </b></td>
                </tr>
                <tr>
                    <td height="14.2"></td>
                    <td style="font-size:12;"><b>{{ $remision->cliente->name }}</b></td>
                    <td></td><td></td>
                    <td style="font-size:9; text-align: center;">
                        CREDITO {{ strtoupper($remision->cliente->condiciones_pago) }}
                    </td>
                    <td></td>
                    <td  rowspan="4" colspan="2" style="text-align: justify; vertical-align: top; font-size:10;">{{ $remision->cliente->direccion }}</td>
                </tr>
                <tr>
                    <td height="14.2"></td><td></td><td></td><td></td>
                    <td style="font-size:9;"><b>CONTACTO</b></td>
                </tr>
                <tr>
                    <td height="14.2"></td><td></td><td></td><td></td>
                    <td style="font-size:9; text-align: center;">{{ $remision->cliente->contacto }}</td>
                </tr>
                <tr>
                    <td height="14.2"></td><td></td><td></td><td></td>
                    <td style="font-size:9;">{{ $remision->cliente->telefono }}</td>
                </tr>
                <tr><td height="19.5"></td></tr>
                <tr><td height="15"></td></tr>
                <tr><td height="15.7"></td></tr>
                <tr><td height="15.7"></td></tr>
                <tr><td height="15.7"></td></tr>
                <tr><td height="15.7"></td></tr>
                <tr><td height="15.7"></td></tr>
                <tr><td height="15.7"></td></tr>
                @foreach($remision->datos as $dato)
                    <tr>
                        <td height="15.7"></td>
                        <td></td>
                        <td style="font-size:10;">{{ $dato->libro->ISBN }}</td>
                        <td style="font-size:10;">{{ $dato->libro->titulo }}</td>
                        <td></td>
                        <td style="font-size:10; text-align: center;">{{ number_format($dato->unidades) }}</td>
                        <td style="font-size:10; text-align: center;">${{ number_format($dato->costo_unitario, 2) }}</td>
                        <td style="font-size:10; text-align: center;">${{ number_format($dato->total, 2) }}</td>
                    </tr>
                @endforeach
                <tr><td height="15.7"></td></tr>
                <tr><td height="15.7"></td></tr>
                <tr><td height="15.7"></td></tr>
                <tr><td height="15.7"></td></tr>
                <tr><td height="15.7"></td></tr>
                <tr><td height="15.7"></td></tr>
                <tr><td height="15.7"></td></tr>
                <tr><td height="15.7"></td></tr>
                <tr><td height="15.7"></td></tr>
                <tr><td height="15.7"></td></tr>
                <tr><td height="15.7"></td></tr>
                <tr><td height="15.7"></td></tr>
                <tr><td height="15.7"></td></tr>
                <tr><td height="15.7"></td></tr>
                <tr><td height="15.7"></td></tr>
                <tr><td height="21"></td></tr>
                <tr><td height="17.2"></td></tr>
                <tr><td height="17.2"></td></tr>
                <tr><td height="13.5"></td></tr>
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
