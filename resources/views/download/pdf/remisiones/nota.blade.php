<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Nota de remisión</title>
        <!-- Styles -->
        <style>
            h1{
                font-size:18px;
            }
            p{
                font-size: 10px;
                line-height: 1.1;
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
            .bordesVer{
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
                font-size:12px;
            }
            #DFM{
                text-align:center;
                font-weight: bold;
            }
            #tdizq {
                text-align:left;
            }
            #tdder{
                text-align:right;
            }
            #tdcent{
                text-align:center;
            }
            #folio{
                text-align:left;
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
            #justText{
                text-align: justify; vertical-align: top;
            }
        </style>
    </head>
    <body>
        <div>
            <table>
                <tr>
                    <th class="sinBorde" id="tdizq">REMISION No.</th>
                    <th class="sinBorde" id="tdder"><p>FECHA</p></th>
                </tr>
                <tr>
                    <td class="sinBorde" id="folio">{{$remision->id}}</td>
                    <td class="sinBorde" id="tdder">{{ $fecha->format('d/m/Y') }}</td>
                </tr>
            </table><br>
            <table style="width:100%" >
                <tr>
                    <th style="width:33%">CLIENTE</th>
                    <th style="width:33%">CONDICIONES DE PAGO</th> 
                    <th style="width:34%">DIRECCIÓN DE ENTREGA</th>
                </tr>
                <tr>
                    <td rowspan="3" id="justText">
                        <p>{{ $remision->cliente->name }}</p>
                    </td>
                    <td id="tdcent">
                        <p>CREDITO {{ strtoupper($remision->cliente->condiciones_pago) }}</p>
                    </td>
                    <td rowspan="3" id="justText">  
                        <p>{{ $remision->cliente->direccion }}</p>
                    </td>
                </tr>
                <tr>
                    <td style="width:33%" id="contacto">CONTACTO</td>
                </tr>
                <tr>
                    <td>
                        {{ $remision->cliente->contacto }}<br>
                        <b>Teléfono</b>: {{ $remision->cliente->telefono }}<br>
                        <b>E-mail</b>: {{ $remision->cliente->email }}
                    </td> 
                </tr>
            </table>
            <br>
            <table style="width:100%">
                <tr>
                    <th >ISBN</th>
                    <th >TITULO</th> 
                    <th >UNIDADES</th>
                    <th >COSTO UNITARIO</th>
                    <th >COSTO TOTAL</th>
                </tr>
                @foreach($remision->datos as $dato)
                <tr>
                    <td class="bordesVer" style="width:20%">{{ $dato->libro->ISBN }}</td>
                    <td class="bordesVer" style="width:25%">{{ $dato->libro->titulo }}</td> 
                    <td class="bordesVer" style="width:15%" id="tdcent">{{ number_format($dato->unidades) }}</td>
                    <td class="bordesVer" style="width:20%"  id="tdcent">$ {{ number_format($dato->costo_unitario, 2) }}</td>
                    <td class="bordesVer" style="width:20%"  id="tdcent">$ {{ number_format($dato->total, 2) }}</td>
                </tr>
                @endforeach
                <tr>
                    <td class="sinBorde"class="sinBorde" colspan="2" rowspan="2"></td>
                    <td class="bordesVerTot" colspan="2" id="total">TOTAL:</td>
                    <td class="bordesVer"id="total">$ {{ number_format($remision->total, 2) }}</td>
                </tr>
                <tr>
                    <td class="sinBorde" id="total" colspan="3">
                        <br><br><br><br><br><br>{{ strtoupper($total_salida) }} 00/100 MN
                    </td>       
                </tr>
            </table>
            
            <footer>
                <table>
                    <tr>
                        <td>
                            <p class="izq">
                            <p> Av. del Taller # 460, Col. Jardín Balbuena C. P. 15900 <br/>Del. Venustiano Carranza, Ciudad de México.<br/>Tel: 55-5803-64-15        mail: tere.omega1@hotmail.com</p>
                            </p>
                        </td>
                    </tr>
                </table>
            </footer>
        </div>
    </body>
</html>
