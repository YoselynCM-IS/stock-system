<template>
    <div>
        <b-table v-if="!load" hover class="mt-3" responsive
            :items="remclientesData.data" :fields="fields">
            <template v-slot:cell(index)="row">
                {{ row.index + 1 }}
            </template>
            <template v-slot:cell(total)="row">${{ row.item.total | formatNumber }}</template>
            <template v-slot:cell(total_devolucion)="row">${{ row.item.total_devolucion | formatNumber }}</template>
            <template v-slot:cell(total_pagar)="row">${{ row.item.total_pagar | formatNumber }}</template>
            <template v-slot:cell(total_pagos)="row">${{ row.item.total_pagos | formatNumber }}</template>
            <template v-slot:cell(ver_pagos)="row">
                <b-button
                    :href="`/cortes/details_cliente/${row.item.cliente_id}`" target="blank" 
                    variant="info" pill>Mostrar
                </b-button>
            </template>
            <template #thead-top="row">
                <tr>
                    <th colspan="2"></th>
                    <th>${{ total_salida | formatNumber }}</th>
                    <th>${{ total_pagos | formatNumber }}</th>
                    <th>${{ total_devolucion | formatNumber }}</th>
                    <th>${{ total_pagar | formatNumber }}</th>
                </tr>
            </template>
        </b-table>
        <load-component v-else></load-component>
    </div>
</template>

<script>
import LoadComponent from '../../funciones/LoadComponent.vue';
import formatNumber from '../../../mixins/formatNumber';
import AcumTotalesRC from '../../../mixins/AcumTotalesRC';
export default {
    components: { LoadComponent },
    mixins: [formatNumber,AcumTotalesRC],
    props: ['remclientesData', 'load'],
    data(){
        return {
            fields: [
                {key: 'index', label: 'N.'},
                {key: 'name', label: 'Cliente'}, 
                {key: 'total', label: 'Salida'}, 
                {key: 'total_pagos', label: 'Pagado'},
                {key: 'total_devolucion', label: 'Devolución'}, 
                {key: 'total_pagar', label: 'Pagar'},
                {key: 'ver_pagos', label: 'Pago(s)'},
            ]
        }
    }
}
</script>

<style>

</style>