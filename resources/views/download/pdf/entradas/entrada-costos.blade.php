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
            #tdcent{
                text-align:center;
            }
            #tdder{
                text-align:right;
            }
            label{
                font-size:13px;
            }
        </style>
    </head>
    <body>
        <div>
            <main>
                <div>
                    <label><b>Folio: {{ $entrada->folio }}</b></label><br>
                    <label><b>Editorial: {{ $entrada->editorial }}</b> </label><br>
                    <label><b>Fecha de creación: {{ $entrada->created_at->format('Y-m-d') }}</b> </label><br>
                    <br>
                    <table style="width:100%">
                        <tr>
                            <th>ISBN</th>
                            <th>Libro</th> 
                            <th>Costo unitario</th>
                            <th>Unidades</th>
                            <th>Subtotal</th>
                        </tr>
                        @foreach($entrada->registros as $registro)
                            <tr>
                                <td class="bordesVer" style="width:15%" id="tdizq">{{ $registro->libro->ISBN }}</td> 
                                <td class="bordesVer" style="width:40%" id="tdizq">{{ $registro->libro->titulo }}</td> 
                                <td class="bordesVer" style="width:15%" id="tdder">${{ number_format($registro->costo_unitario, 2) }}</td>
                                <td class="bordesVer" style="width:15%" id="tdcent">{{ number_format($registro->unidades) }}</td>
                                <td class="bordesVer" style="width:15%" id="tdder">${{ number_format($registro->total, 2) }}</td>
                            </tr>
                        @endforeach  
                        <tr>
                            <td class="sinBorde"></td><td class="sinBorde"></td>
                            <td class="sinBorde"></td>
                            <td class="sinBorde" id="tdcent"><b>{{ number_format($entrada->unidades) }}</b></td>
                            <td class="sinBorde" id="tdder"><b>${{ number_format($entrada->total, 2) }}</b></td>
                        </tr>
                    </table>
                    @if($entrada->repayments->count() > 0)
                        <br><br>
                        <h5><b>Pagos</b></h5>
                        <table style="width:100%">
                            <tr>
                                <th class="sinBorde"></th>
                                <th>Pago</th>
                                <th>Fecha de pago</th>
                                <th class="sinBorde"></th>
                            </tr>
                            @foreach($entrada->repayments as $repayment)
                                <tr>
                                    <td class="sinBorde" style="width:15%"></td>
                                    <td class="bordesVer" style="width:15%" id="tdder">${{ number_format($repayment->pago, 2) }}</td>
                                    <td class="bordesVer" style="width:20%" id="tdcent">{{ $repayment->created_at->format('Y-m-d') }}</td>
                                    <td class="sinBorde" style="width:50%"></td>
                                </tr>
                            @endforeach
                            <tr>
                                <td class="sinBorde" id="tdder"><b>TOTAL</b></td>
                                <td class="sinBorde" id="tdder"><b>${{ number_format($entrada->total_pagos, 2) }}</b></td>
                                <td class="sinBorde"></td><td class="sinBorde"></td>
                            </tr>
                            <tr>
                                <td class="sinBorde" id="tdder"><b>TOTAL PENDIENTE</b></td>
                                <td class="sinBorde" id="tdder"><b>${{ number_format($entrada->total - $entrada->total_pagos, 2) }}</b></td>
                                <td class="sinBorde"></td><td class="sinBorde"></td>
                            </tr>
                        </table>
                    @endif
                </div>
            </main>
        </div>
    </body>
</html>
