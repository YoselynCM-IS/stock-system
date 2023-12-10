<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Entrada</title>
        <!-- Styles -->
        <style>
            table{
                border-collapse: collapse;
                width: 100%;   
            }
            th, td {
                border-collapse: collapse;
                padding: 1px;
                border-left:1px solid #ddd;
                border-right:1px solid #ddd;
                border-top:1px solid #ddd;
                border-bottom:1px solid #ddd;
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
            .sinBorde{
                border-width: 0px;
            }
            .bordesVer
            {
                border-left:1px solid #ddd;
                border-right:1px solid #ddd;   
            }
            #tdizq{
                text-align:left;
            }
            #tdder{
                text-align:right;
            }
            #tdcent{
                text-align:center;
            }
            label{
                font-size:13px;
            }
        </style>
    </head>
    <body>
        <div>
            <div>
                <label><b>Fecha: {{ $fecha }}</b> </label><br>
                @if($editorial != 'TODAS')
                    <label><b>Editorial: </b> {{ $editorial }}</label><br>
                @endif
                @if($final != '0000-00-00')
                    <label><b>De:</b> {{ $inicio }} - <b>A:</b> {{ $final }}</label><br>
                @endif
                <br>
                <table style="width:100%">
                    @if(auth()->user()->role_id === 3)
                        <tr>
                            <th>Folio</th>
                            <th>Editorial</th>
                            <th>Unidades</th>
                            <th>Fecha de creación</th>
                        </tr>
                        @foreach($entradas as $entrada)
                            <tr>
                                <td class="bordesVer" style="width:15%" id="tdizq">{{ $entrada->folio }}</td> 
                                <td class="bordesVer" style="width:55%" id="tdizq">{{ $entrada->editorial }}</td>
                                <td class="bordesVer" style="width:15%" id="tdcent">{{ number_format($entrada->unidades) }}</td>
                                <td class="bordesVer" style="width:15%" id="tdcent">{{ $entrada->created_at->format('Y-m-d') }}</td>
                            </tr>
                        @endforeach  
                        <tr>
                            <td class="sinBorde"></td><td class="sinBorde"></td>
                            <td class="sinBorde" id="tdcent"><b>{{ number_format($total_unidades) }}</b></td>
                        </tr>
                    @else
                        <tr>
                            <th>Folio</th>
                            <th>Fecha de creación</th>
                            <th>Editorial</th>
                            <th>Unidades</th>
                            <th>Total</th>
                            <th>Pagos</th>
                            <th>Devolucion</th>
                            <th>Pendiente</th>
                        </tr>
                        @foreach($entradas as $entrada)
                            <tr>
                                <td class="bordesVer" style="width:10%" id="tdizq">{{ $entrada->folio }}</td> 
                                <td class="bordesVer" style="width:10%" id="tdcent">{{ $entrada->created_at->format('Y-m-d') }}</td>
                                <td class="bordesVer" style="width:30%" id="tdizq">{{ $entrada->editorial }}</td>
                                <td class="bordesVer" style="width:10%" id="tdcent">{{ number_format($entrada->unidades, 2) }}</td>
                                <td class="bordesVer" style="width:10%" id="tdder">${{ number_format($entrada->total, 2) }}</td>
                                <td class="bordesVer" style="width:10%" id="tdder">${{ number_format($entrada->total_pagos, 2) }}</td>
                                <td class="bordesVer" style="width:10%" id="tdder">${{ number_format($entrada->total_devolucion, 2) }}</td>
                                <td class="bordesVer" style="width:10%" id="tdder">${{ number_format($entrada->total - $entrada->total_pagos, 2) }}</td>
                            </tr>
                        @endforeach  
                        <tr>
                            <td class="sinBorde"></td><td class="sinBorde"></td><td class="sinBorde"></td>
                            <td class="sinBorde" id="tdcent"><b>{{ number_format($total_unidades) }}</b></td>
                            <td class="sinBorde" id="tdder"><b>${{ number_format($total) }}</b></td>
                            <td class="sinBorde" id="tdder"><b>${{ number_format($total_pagos) }}</b></td>
                            <td class="sinBorde" id="tdder"><b>${{ number_format($total_devolucion) }}</b></td>
                            <td class="sinBorde" id="tdder"><b>${{ number_format($total_pendiente) }}</b></td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>
    </body>
</html>
