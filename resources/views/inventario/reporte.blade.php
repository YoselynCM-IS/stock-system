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
            .bordesVerTot{
                border-right:1px solid #ddd;
            }
            #qr{
                align:center;
            }
            #contacto{
                text-align:center;
                font-weight: bold;
            }
            #DFM{
                text-align:center;
                font-weight: bold;
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
            #folio{
                text-align:center;
                color:red;
                font-size:14px;
            }
            #total{
                text-align:right;
                font-weight: bold;
                font-size:14px;
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
                    <br>
                    <table style="width:100%">
                        <tr>
                            <th>ISBN</th>
                            <th>Libro</th> 
                            <!-- <th>Costo unitario</th> -->
                            <th>Unidades</th>
                            <!-- <th>Subtotal</th> -->
                        </tr>
                        @foreach($registros as $registro)
                            <tr>
                                <td class="bordesVer" style="width:25%" id="tdizq">{{ $registro->libro->ISBN }}</td> 
                                <td class="bordesVer" style="width:25%" id="tdizq">{{ $registro->libro->titulo }}</td> 
                                <!-- <td class="bordesVer" style="width:15%" id="tdder">$ {{ $registro->costo_unitario }}</td> -->
                                <td class="bordesVer" style="width:15%" id="tdcent">{{ $registro->unidades }}</td>
                                <!-- <td class="bordesVer" style="width:15%" id="tdder">$ {{ $registro->total }}</td> -->
                            </tr>
                        @endforeach  
                        <tr>
                            <td class="sinBorde"></td><td class="sinBorde"></td>
                            <!-- <td class="sinBorde"></td> -->
                            <td class="sinBorde" id="tdcent"><b>{{ $entrada->unidades }}</b></td>
                            <!-- <td class="sinBorde" id="tdder"><b>$ {{ $entrada->total }}</b></td> -->
                        </tr>
                    </table>
                </div>
            </main>
        </div>
    </body>
</html>
