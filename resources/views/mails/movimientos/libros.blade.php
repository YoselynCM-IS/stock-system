<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
	<head>
	    <meta charset="utf-8">
	    <meta http-equiv="X-UA-Compatible" content="IE=edge">
	    <meta name="viewport" content="width=device-width, initial-scale=1">
	    <title>Majestic Education</title>
	</head>
	<body style="background-color: white; color: black;">
        <p>
            Movimientos realizados en el sistema de inventario el día {{ $fecha }}.
        </p>
        <p>
            Se muestra lo que entro (entradas, devolución en remisiones, devolución en salidas y devolución en promociones) y lo que salió (salidas a Querétaro, devolución en entradas, remisiones, notas, donaciones y promociones).
        </p>
	</body>
</html>