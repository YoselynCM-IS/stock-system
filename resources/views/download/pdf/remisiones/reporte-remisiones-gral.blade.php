<!doctype html>
<html>
    <head>
        <title>Reporte</title>
        <style>
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
            #tdcent{
                text-align:center;
            }
            #tdder{
                text-align:right;
            }
        </style>
    </head>
    <body>
        <div>
            <main>
                <div>
                    <h5><b>Fecha:</b> {{ $fecha }}</h5>
                    @if($inicio != '0000-00-00' && $final != '0000-00-00')
                        <h6><b>De:</b> {{ $inicio }} - <b>A:</b> {{ $final }}</h6>
                    @endif
                    @if($estado === 'cancelado')
                        <h4>Remisiones CANCELADAS</h4>
                    @endif
                    @if($estado === 'no_entregado')
                        <h4>Remisiones NO ENTREGADAS</h4>
                    @endif
                    @if($estado === 'entregado')
                        <h4>Remisiones ENTREGADAS</h4>
                    @endif
                    @if($estado === 'pagado')
                        <h4>Remisiones PAGADAS</h4>
                    @endif
                    @include('download.partials.pdf.table', ['remisiones' => $remisiones, 'totales' => $totales])
                </div>
            </main>
        </div>
    </body>
</html>
