<template>
    <div>
        <b-table :items="entradas" :fields="fields">
            <template v-slot:cell(index)="row">{{ row.index + 1}}</template>
            <template v-slot:cell(unidades)="row">{{ row.item.unidades | formatNumber }}</template>
            <template v-slot:cell(total)="row">${{ row.item.total | formatNumber }}</template>
            <template v-slot:cell(total_pagos)="row">${{ row.item.total_pagos | formatNumber }}</template>
            <template v-slot:cell(total_devolucion)="row">${{ row.item.total_devolucion | formatNumber }}</template>
            <template v-slot:cell(total_pendiente)="row">
                ${{ (row.item.total - (row.item.total_pagos + row.item.total_devolucion)) | formatNumber }}
            </template>
        </b-table>
    </div>
</template>

<script>
import formatNumber from '../../../mixins/formatNumber';
export default {
    props: ['entradas'],
    mixins: [formatNumber],
    data(){
        return {
            fields: [
                {key: 'index', label: 'N.'},
                'folio',
                {key: 'created_at', label: 'Fecha de registro'},
                'unidades', 'total',
                {key: 'total_devolucion', label: 'Devolución'},
                {key: 'total_pendiente', label: 'Pagar'}
            ],
        }
    }
}
</script>

<style>

</style>