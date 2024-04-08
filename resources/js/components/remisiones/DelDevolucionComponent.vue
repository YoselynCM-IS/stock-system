<template>
    <div>
        <b-table :items="remision.fechas" :fields="fields">
            <template v-slot:cell(index)="data">
                {{ data.index + 1 }}
            </template>
        </b-table>
    </div>
</template>

<script>
export default {
    props: ['remision'],
    data() {
        return {
            fields: [
                { key: 'index', label: 'N.' }, 
                { key: 'libro.titulo', label: 'Titulo' },
                { key: 'unidades', label: 'Unidades' },
                { key: 'defectuosos', label: 'Defectuosos' },
                { key: 'total', label: 'Total' },
                { key: 'created_at', label: 'Fecha' },
                { key: 'entregado_por', label: 'Entregado por' },
                { key: 'creado_por', label: 'Creado por' },
                { key: 'actions', label: '' },
            ]
        }
    }
}
</script>

<style>

</style>