<template>
    <div>
        <b-table hover :items="depositos" :fields="fields">
            <template v-slot:cell(index)="row">
                {{ row.index + 1 }}
            </template>
            <template v-slot:cell(pago)="row">
                ${{ row.item.pago | formatNumber }}
            </template>
            <template v-slot:cell(created_at)="row">
                {{ row.item.created_at | moment }}
            </template>
            <template #thead-top="row">
                <tr>
                    <th colspan="2"></th>
                    <th><h5>${{ total_depositos | formatNumber }}</h5></th>
                </tr>
            </template>
        </b-table>
    </div>
</template>

<script>
import formatNumber from '../../../mixins/formatNumber';
import moment from '../../../mixins/moment';
export default {
    props: ['depositos'],
    mixins: [formatNumber,moment],
    data(){
        return {
            fields: [
                {key: 'index', label: 'No.'},
                {key: 'created_at', label: 'Fecha de pago'},
                'pago'
            ],
            total_depositos: 0
        }
    },
    created: function(){
        this.depositos.forEach(deposito => {
            this.total_depositos += deposito.pago;
        });
    },
}
</script>

<style>

</style>