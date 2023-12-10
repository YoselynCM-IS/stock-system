@foreach($promotions as $promotion)
    <table>
        <tr>
            <th><b>Folio</b></th>
            <th><b>Plantel</b></th>
            <th><b>Unidades</b></th>
            <th><b>Fecha de creación</b></th>
        </tr>
        <tr>
            <td>{{ $promotion->folio }}</td> 
            <td>{{ $promotion->plantel }}</td>
            <td>{{ $promotion->unidades }}</td>
            <td>{{ $promotion->created_at->format('Y-m-d') }}</td>
        </tr>
    </table>
    <table>
        <tr>
            <th><b>ISBN</b></th>
            <th><b>Libro</b></th>
            <th><b>Unidades</b></th>

        </tr>
        @foreach($promotion->departures as $departure)
            <tr>
                <td>{{ $departure->libro->ISBN }}</td>
                <td>{{ $departure->libro->titulo }}</td>
                <td>{{ $departure->unidades }}</td>
            </tr>
        @endforeach
    </table><br>
@endforeach 