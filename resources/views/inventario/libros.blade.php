<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Libros</title>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
        <!-- Styles -->
        <style>
            @page {
                margin: 160px 50px;
                }
            html, body {
                background-color: #fff;
                color: #000000;
                font-family:'Nunito', sans-serif;
                font-weight: 200;
                height: 100vh;
                margin: 0;
            }
            h1{
                font-size:18px;
            }
            p{
                font-size:10px;
                line-height:1.1;
            }
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
            footer {
                color:black;
                position: fixed;
                left: 100px;
                bottom: 0px;
                right: 0px;
                height: 100px;
                border-bottom: 0px solid #ddd;
            }
            footer .page:after {
                content: counter(page);
            }
            footer table {
                width: 100%;
            }
            footer p {
                text-align: center;
            }
            footer .izq {
                text-align: center;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <main class="py-4">
                <div align="center">
                    <table style="width:100%">
                        <tr>
                            <th>N.</th>
                            <th>ISBN</th> 
                            <th>TITULO</th>
                            <th>EDITORIAL</th>
                            <th>PIEZAS</th>
                        </tr>
                        @foreach($libros as $libro)
                            <tr>
                                <td class="bordesVer" style="width:5%" id="tdder">{{ $libro->id }}</td>
                                <td class="bordesVer" style="width:30%" id="tdizq">{{ $libro->ISBN }}</td> 
                                <td class="bordesVer" style="width:40%" id="tdder">{{ $libro->titulo }}</td>
                                <td class="bordesVer" style="width:15%" id="tdder">{{ $libro->editorial }}</td>
                                <td class="bordesVer" style="width:10%" id="tdder">{{ $libro->piezas }}</td>
                            </tr>
                        @endforeach  
                    </table>
                </div>
            </main>
        </div>
    </body>
</html>
