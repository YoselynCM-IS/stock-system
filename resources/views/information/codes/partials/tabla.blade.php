<div class="table-responsive">
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th scope="col">N.</th>
                @if(!$diferencia)  
                    <th scope="col">ISBN</th>
                @endif
                <th scope="col">TITULO</th>
                <th scope="col">EXISTENCIA</th>
            </tr>
        </thead>
        <tbody>
            @foreach($libros as $libro)
                <tr>
                    <th scope="row">{{$loop->index + 1}}</th>
                    @if(!$diferencia)  
                        <td>{{ $libro->libro->ISBN }}</td>
                        <td>{{ $libro->libro->titulo }}</td>  
                        <td>{{ $libro->inventario }}</td>
                    @else
                        <td>
                            <b>{{ $libro['titulo'] }}</b>
                            <ul>
                                <li>{{$libro['fisico']}}</li>
                                <li>{{$libro['digital']}}</li>
                            </ul>
                        </td>  
                        <td>{{ $libro['piezas'] }}</td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
</div>