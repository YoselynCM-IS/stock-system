<td>
    @foreach($elementos as $elemento)
        @if($cliente)
            {{ $elemento->cliente }}
        @endif
        {{ $etiqueta }}: {{ $elemento->folio }} = {{ $elemento->unidades }} unidades <br>
    @endforeach
</td>