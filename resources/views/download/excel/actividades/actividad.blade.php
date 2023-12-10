<table>
    <tr>
        <td><b>NOMBRE: </b></td>
        <td>{{ $actividad->nombre }}</td>
    </tr>
    <tr>
        <td><b>TIPO: </b></td>
        <td>{{ $actividad->tipo }}</td>
    </tr>
    <tr>
        <td><b>DESCRIPCIÓN: </b></td>
        <td>{!! $actividad->descripcion !!}</td>
    </tr>
    <tr>
        <td><b>FECHA: </b></td>
        <td>{{ $actividad->fecha}}</td>
    </tr>
    <tr>
        <td><b>RESULTADO: </b></td>
        <td>{{ $actividad->exitosa }}</td>
    </tr>
    <tr>
        <td><b>OBSERVACIONES: </b></td>
        <td>{!! $actividad->observaciones !!}</td>
    </tr>
    @foreach($actividad->clientes as $cliente)
        <tr>
            <td>{{ $cliente->id }}</td>
        </tr>
    @endforeach
</table>