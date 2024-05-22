<template>
    <div>
        <h5>Entrada - Folio: <b>{{ entrada.folio }}</b></h5><hr>
        <!-- <b-table :items="entrada.registros"></b-table> -->
        <!-- {{ entrada }} -->
    </div>
</template>

<script>
export default {
    props: ['entrada'],
    data() {
        return {
            fields: [
                { key: 'index', label: 'N.' },
                { key: 'libro.titulo', label: 'Libro' },
                { key: 'unidades', label: 'Unidades' },
                { key: 'costo_unitario', label: 'Costo unitario' },
                { key: 'costo_unitario', label: 'Costo unitario' }
            ]
        }
    }
}
</script>

<style>

</style>