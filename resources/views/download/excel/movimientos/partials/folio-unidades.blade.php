<td>
    @foreach($elementos as $elemento)
        {{ $elemento->fecha }}
        @if($cliente)
            {{ $elemento->cliente }}
        @endif
        {{ $etiqueta }}: {{ $elemento->folio }} = {{ $elemento->unidades }} <br>
    @endforeach
</td>