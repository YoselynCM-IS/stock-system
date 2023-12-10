<table>
    <tr>
        <th><b>Folio</b></th>
        <th><b>Plantel</b></th>
        <th><b>Unidades</b></th>
        <th><b>Fecha de creación</b></th>
    </tr>
    @foreach($promotions as $promotion)
        <tr>
            <td>{{ $promotion->folio }}</td> 
            <td>{{ $promotion->plantel }}</td>
            <td>{{ $promotion->unidades }}</td>
            <td>{{ $promotion->created_at->format('Y-m-d') }}</td>
        </tr>
    @endforeach  
    <tr>
        <td></td><td></td>
        <td><b>{{ $total_unidades }}</b></td>
        <td></td>
    </tr>
</table>