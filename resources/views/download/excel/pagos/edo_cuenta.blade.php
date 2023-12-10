<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Edo. cta.</title>
        <!-- Styles -->
        <style>
            table{
                border-collapse: collapse;
                width: 100%;   
            }
            th, td {
                border-collapse: collapse;
                padding: 1px;
                border-left:1px solid #fff;
                border-right:1px solid #fff;
                border-top:1px solid #fff;
                border-bottom:1px solid #fff;
            }
            th{
                border-collapse: collapse;
                text-align:center;
                font-size:12px;
            }
            td{
                border-collapse: collapse;
                text-align:left;
                font-size:10px;
            }
            #izquierda {
                width: 60%;
                float:left;
            }
            #centro {
                width: 5%;
                float:left;
            }
            #derecha {
                width: 35%;
                float:right;
            }
            #idSpace {
                padding-top: 30px;
            },
            #idSpace2 {
                padding-top: 70px;
            }
        </style>
    </head>
    <body>
    <div>
        <table>
            <tr>
                <td><b>CLIENTE</b></td>
                <td><b>FECHA</b></td>
            </tr>
            <tr>
                <td>{{ $remcliente->cliente->name }}</td>
                <td>{{ date_format($date,'Y-m-d') }}</td>
            </tr>
        </table>
        <div id="izquierda">
            <table>
                <!-- REMISIONES -->
                <tr>
                    <td id="idSpace"><b>REMISIONES</b></td>
                    @if($remisiones->count() == 0)
                        <td id="idSpace"></td>
                        <td id="idSpace" style="text-align:right;"><b>$0.00</b></td>
                    @endif
                </tr>
                @if($remisiones->count() > 0)
                    <tr>
                        <td><b>FECHA</b></td>
                        <td><b>FOLIO</b></td>
                        <td style="text-align:right;"><b>TOTAL</b></td>
                    </tr>
                    @foreach($remisiones as $remision)
                        <tr>
                            <td>{{ $remision->fecha_creacion }}</td>
                            <td>{{ $remision->id }}</td>
                            <td style="text-align:right;">
                                ${{ number_format($remision->total, 2) }}
                            </td>
                        </tr>
                    @endforeach 
                    <tr>
                        <td></td><td></td>
                        <td style="text-align:right;"><b>${{ number_format($total_remisiones, 2) }}</b></td>
                    </tr>
                @endif
                <!-- DEVOLUCIONES -->
                @if($fechas->count() > 0)
                    <!-- <tr></tr> -->
                    <tr>
                        <td id="idSpace"><b>DEVOLUCIONES</b></td>
                    </tr>
                    <tr>
                        <td><b>FECHA</b></td>
                        <td><b>FOLIO</b></td>
                        <td><b>TOTAL</b></td>
                        <td colspan="2"><b>LIBRO</b></td>
                        <td><b>UNIDADES</b></td>
                    </tr>
                    @foreach($fechas as $fecha)
                        <tr>
                            <td>{{ $fecha->fecha_devolucion }}</td>
                            <td>{{ $fecha->remisione_id }}</td>
                            <td style="text-align:right;">
                                ${{ number_format($fecha->total, 2) }}
                            </td>
                            <td colspan="2">{{ $fecha->libro->titulo }}</td>
                            <td>{{ $fecha->unidades }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td><b></b></td><td><b></b></td>
                        <td style="text-align:right;"><b>${{ number_format($total_fechas, 2) }}</b></td>
                    </tr>
                @else
                    <tr>
                        <td id="idSpace"><b>DEVOLUCIONES</b></td>
                        <td id="idSpace"></td>
                        <td id="idSpace" style="text-align:right;"><b>$0.00</b></td>
                    </tr>
                @endif
            </table>
        </div>
        <div id="centro"></div>
        <div id="derecha">
            <table>
                <!-- PAGOS -->
                <tr>
                    <td id="idSpace"><b>PAGOS</b></td>
                    @if($depositos->count() == 0 && $remdepositos->count() == 0)
                        <td id="idSpace"></td>
                        <td id="idSpace" style="text-align:right;"><b>$0.00</b></td>
                    @endif
                </tr>
                @if($depositos->count() > 0 || $remdepositos->count() > 0)
                    @if($depositos->count() > 0)
                        <tr>
                            <td><b>FECHA</b></td>
                            <td><b>FOLIO</b></td>
                            <td style="text-align:right;"><b>PAGO</b></td>
                        </tr>
                        @foreach($depositos as $deposito)
                            <tr>
                                <td>{{ $deposito->created_at->format('Y-m-d') }}</td>
                                <td>{{ $deposito->remisione_id }}</td>
                                <td style="text-align:right;">
                                    ${{ number_format($deposito->pago, 2) }}
                                </td>
                            </tr>
                        @endforeach
                        <!-- <tr></tr> -->
                    @endif
                    @if($remdepositos->count() > 0)
                        <tr>
                            <td><b>FECHA</b></td>
                            <td><b></b></td>
                            <td style="text-align:right;"><b>PAGO</b></td>
                        </tr>
                        @foreach($remdepositos as $remdeposito)
                            <tr>
                                <td>{{ $remdeposito->created_at->format('Y-m-d') }}</td>
                                <td>GENERAL</td>
                                <td style="text-align:right;">
                                    ${{ number_format($remdeposito->pago, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    @endif
                    <tr>
                        <td></td><td></td>
                        <td style="text-align:right;"><b>${{ number_format($total_remdepositos, 2) }}</b></td>
                    </tr>
                @endif
                <tr>
                    <td id="idSpace2"></td>
                </tr>
                <!-- <tr>
                    <td></td>
                    <td><b>TOTAL</b></td>
                    <td style="text-align:right;">
                        ${{ number_format($remcliente->total, 2) }}
                    </td>
                </tr> -->
                <!-- <tr>
                    <td></td>
                    <td><b>PAGADO</b></td>
                    <td style="text-align:right;">
                        ${{ number_format($remcliente->total_pagos, 2) }}
                    </td>
                </tr> -->
                <!-- <tr>
                    <td></td>
                    <td><b>DEVOLUCIÓN</b></td>
                    <td style="text-align:right;">
                        ${{ number_format($remcliente->total_devolucion, 2) }}
                    </td>
                </tr> -->
                <tr>
                    <td></td>
                    <td><b>PAGAR</b></td>
                    <td style="text-align:right;">
                        ${{ number_format($remcliente->total_pagar, 2) }}
                    </td>
                </tr>
            </table>
        </div>
    </div>
    </body>
</html>
