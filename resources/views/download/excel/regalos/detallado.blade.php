@foreach($regalos as $regalo)
    <table>
        <tr>
            <th><b>Plantel</b></th>
            <th><b>Unidades</b></th>
            <th><b>Fecha de creación</b></th>
        </tr>
        <tr>
            <td>{{ $regalo->plantel }}</td>
            <td>{{ $regalo->unidades }}</td>
            <td>{{ $regalo->created_at->format('Y-m-d') }}</td>
        </tr>
    </table>
    <table>
        <tr>
            <th><b>ISBN</b></th>
            <th><b>Libro</b></th>
            <th><b>Unidades</b></th>

        </tr>
        @foreach($regalo->donaciones as $donacion)
            <tr>
                <td>{{ $donacion->libro->ISBN }}</td>
                <td>{{ $donacion->libro->titulo }}</td>
                <td>{{ $donacion->unidades }}</td>
            </tr>
        @endforeach
    </table><br>
@endforeach 