<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>regalo</title>
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
                    <label><b>Plantel: {{ $regalo->plantel }}</b> </label><br>
                    <label><b>Fecha de creación: {{ $regalo->created_at->format('Y-m-d') }}</b> </label><br>
                    <br>
                    <table style="width:100%">
                        <tr>
                            <th>ISBN</th>
                            <th>Libro</th> 
                            <th>Unidades</th>
                        </tr>
                        @foreach($regalo->donaciones as $donacion)
                            <tr>
                                <td class="bordesVer" style="width:15%" id="tdizq">{{ $donacion->libro->ISBN }}</td> 
                                <td class="bordesVer" style="width:40%" id="tdizq">{{ $donacion->libro->titulo }}</td>
                                <td class="bordesVer" style="width:15%" id="tdcent">{{ number_format($donacion->unidades) }}</td>
                            </tr>
                        @endforeach  
                        <tr>
                            <td class="sinBorde"></td><td class="sinBorde"></td>
                            <td class="sinBorde" id="tdcent"><b>{{ number_format($regalo->unidades) }}</b></td>
                        </tr>
                    </table>
                </div>
            </main>
        </div>
    </body>
</html>
