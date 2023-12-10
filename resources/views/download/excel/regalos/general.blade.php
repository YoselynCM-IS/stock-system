<table>
    <tr>
        <th><b>Plantel</b></th>
        <th><b>Unidades</b></th>
        <th><b>Fecha de creación</b></th>
    </tr>
    @foreach($regalos as $regalo)
        <tr>
            <td>{{ $regalo->plantel }}</td>
            <td>{{ $regalo->unidades }}</td>
            <td>{{ $regalo->created_at->format('Y-m-d') }}</td>
        </tr>
    @endforeach  
    <tr>
        <td></td>
        <td><b>{{ $total_unidades }}</b></td>
        <td></td>
    </tr>
</table>