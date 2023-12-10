<template>
    <div>
        <b-table v-if="remisiones.length > 0"
            :items="remisiones" :fields="fieldsRems"
            responsive :tbody-tr-class="rowClass">
            <template v-slot:cell(total)="row">
                ${{ row.item.total | formatNumber }}
            </template>
            <template v-slot:cell(total_devolucion)="row">
                ${{ row.item.total_devolucion | formatNumber }}
            </template>
            <template v-slot:cell(total_pagos)="row">
                ${{ row.item.pagos | formatNumber }}
            </template>
            <template v-slot:cell(total_pagar)="row">
                ${{ row.item.total_pagar | formatNumber }}
            </template>
            <template v-slot:cell(actions)="row">
                <b-button v-if="role_id == 6 && (row.item.total_pagar == row.item.total)" 
                    pill size="sm" variant="primary" @click="moveRemisione(row.item)">
                    <i class="fa fa-exchange"></i>
                </b-button>
            </template>
            <template v-if="showTitle" #thead-top="row">
                <tr>
                    <th colspan="6" class="text-center">Remisiones</th>
                </tr>
            </template>
        </b-table>
        <alert-v-component v-else :text="'remisiones'"></alert-v-component>

        <!-- MODALS -->
        <!-- Mover remisión de corte-->
        <b-modal ref="show-move-rem" hide-footer size="sm"
            :title="`Mover remisión ${form.remisione_id}`">
           <select-corte-component :form="form" :options="options" :move="true"
                        @remsGuardadas="remsGuardadas"></select-corte-component> 
        </b-modal>
    </div>
</template>

<script>
import formatNumber from '../../../mixins/formatNumber';
import AlertVComponent from './AlertVComponent.vue';
import setCortes from '../../../mixins/setCortes';
import toast from '../../../mixins/toast';
import sweetAlert from '../../../mixins/sweetAlert';
export default {
    components: { AlertVComponent },
    props: ['remisiones', 'showTitle', 'role_id'],
    mixins: [formatNumber, setCortes, toast, sweetAlert],
    data(){
        return {
            fieldsRems: [
                { key: 'id', label: 'Folio' },
                { key: 'fecha_creacion', label: 'Fecha de creación' },
                { key: 'total', label: 'Salida' },
                { key: 'total_devolucion', label: 'Devolución' },
                { key: 'total_pagos', label: 'Pagos' },
                { key: 'total_pagar', label: 'Pagar' },
                { key: 'actions', label: '' }
            ],
            form: {
                remisione_id: null,
                corte_id: null
            },
            options: []
        }
    },
    methods: {
        // COLOR VER A REMISIONES PAGADAS
        rowClass(item, type) {
            if (!item) return
            if (item.total_pagar == 0 && (item.pagos > 0 || item.total_devolucion > 0)) return 'table-success'
        },
        // MOVER REMISION
        moveRemisione(remision){
            this.load = true;
            axios.get('/cortes/get_all').then(response => {
                this.form.remisione_id = remision.id;
                this.form.corte_id = null;
                this.cliente_id = remision.cliente_id;
                this.options = this.setCortes(response.data, null);
                this.$refs['show-move-rem'].show();
                this.load = false;
            }).catch(error => {
                this.load = false;
            });
        },
        // REMISION MOVIDA
        remsGuardadas(valor){
            this.$refs['show-move-rem'].hide();
            if (valor) {
                this.messageAlert('center', 'success', 'La remisión se movio correctamente.', null, 'reload');
            } else this.makeToast('warning', 'Se eligió el mismo corte.');
        }
    }
}
</script>

<style>

</style>