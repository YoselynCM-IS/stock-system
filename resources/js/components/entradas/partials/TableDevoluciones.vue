<template>
    <div>
        <b-table :items="devoluciones" :fields="fields">
            <template v-slot:cell(index)="row">{{ row.index + 1}}</template>
            <template v-slot:cell(costo_unitario)="row">${{ row.item.registro.costo_unitario | formatNumber }}</template>
            <template v-slot:cell(total)="row">${{ row.item.total | formatNumber }}</template>
        </b-table>
    </div>
</template>

<script>
import formatNumber from '../../../mixins/formatNumber';
export default {
    props: ['devoluciones'],
    mixins: [formatNumber],
    data(){
        return {
            fields: [
                {key: 'index', label: 'N.'}, 
                {key: 'created_at', label: 'Fecha'},
                {key: 'registro.libro.ISBN', label: 'ISBN'}, 
                {key: 'registro.libro.titulo', label: 'Libro'}, 
                {key: 'costo_unitario', label: 'Costo unitario'}, 
                {key: 'unidades', label: 'Unidades'}, 
                {key: 'total', label: 'Subtotal'}
            ],
        }
    }
}
</script>

<style>

</style>