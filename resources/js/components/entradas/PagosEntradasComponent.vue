<template>
    <div>
        <b-row>
            <b-col>
                <h4 style="color: #170057">Proveedores</h4>
            </b-col>
            <b-col sm="3" class="text-right">
                <b-button variant="dark" href="/descargar_gralEdit" pill>
                    <i class="fa fa-download"></i> Descargar
                </b-button>
            </b-col>
            <b-col sm="3" class="text-right" >
                <b-button v-if="role_id === 1 || role_id == 6" :disabled="load"
                    pill variant="success" @click="newEditorial()">
                    <i class="fa fa-plus-circle"></i> Agregar editorial
                </b-button>
            </b-col>
        </b-row>
        <b-table :items="editoriales" :fields="fieldsEntrada">
            <template v-slot:cell(total)="row">
                ${{ row.item.total | formatNumber }}
            </template>
            <template v-slot:cell(total_pagos)="row">
                ${{ row.item.total_pagos | formatNumber }}
            </template>
            <template v-slot:cell(total_devolucion)="row">
                ${{ row.item.total_devolucion | formatNumber }}
            </template>
            <template v-slot:cell(total_pendiente)="row">
                ${{ row.item.total_pendiente | formatNumber }}
            </template>
            <template v-slot:cell(pagos)="row">
                <b-button target="_blank" variant="info" pill 
                    :href="`/information/entradas/cortes/${row.item.editorial}`">
                    Ver pagos
                </b-button>
            </template>
            <template #thead-top="row">
                <tr>
                    <th colspan="1">&nbsp;</th>
                    <th>${{ eTotals.total | formatNumber }}</th>
                    <th>${{ eTotals.total_pagos | formatNumber }}</th>
                    <th>${{ eTotals.total_devolucion | formatNumber }}</th>
                    <th>${{ eTotals.total_pendiente | formatNumber }}</th>
                    <th colspan="2">&nbsp;</th>
                </tr>
            </template>
        </b-table>
        <b-modal ref="modal-mostrarPagos" :title="entrada.editorial" hide-footer size="xl">
            <b-tabs content-class="mt-3" fill>
                <b-tab title="PAGOS" active>
                    
                </b-tab>
                <b-tab title="DEVOLUCIONES">
                    <b-table :items="entrada.entdevoluciones" :fields="fieldsDev" responsive>
                        <template v-slot:cell(index)="row">{{ row.index + 1}}</template>
                            <template v-slot:cell(costo_unitario)="row">
                                ${{ row.item.costo_unitario | formatNumber }}
                            </template>
                            <template v-slot:cell(unidades)="row">
                                {{ row.item.unidades | formatNumber }}
                            </template>
                            <template v-slot:cell(total)="row">
                                ${{ row.item.total | formatNumber }}
                            </template>
                    </b-table>
                </b-tab>
            </b-tabs>
        </b-modal>
        <b-modal ref="modal-agregarEditorial" title="Agregar editorial" hide-footer size="sm">
            <add-edit-editorial @saveEditorial="saveEditorial"></add-edit-editorial>
        </b-modal>
    </div>
</template>

<script>
import formatNumber from '../../mixins/formatNumber';
import toast from '../../mixins/toast';
import AddEditEditorial from './partials/AddEditEditorial.vue';
import sweetAlert from '../../mixins/sweetAlert';
export default {
    props: ['role_id', 'editoriales'],
    components: {AddEditEditorial},
    mixins: [formatNumber, toast, sweetAlert],
    data(){
        return {
            fieldsEntrada: [
                'editorial', 'total',
                {key: 'total_pagos', label: 'Pagos'},
                {key: 'total_devolucion', label: 'Devolucion'},
                {key: 'total_pendiente', label: 'Pagar'},
                {key: 'pagos', label: ''}
            ],
            eTotals: {
                total: 0,
                total_pagos: 0,
                total_devolucion: 0,
                total_pendiente: 0
            },
            entrada: { 
                editorial: null, 
                total_pagos: null,
                total_pendiente: null,
                entdepositos: [],
                entdevoluciones: []
            },
            fieldsDev: [
                {key: 'index', label: 'N.'},
                {key: 'created_at', label: 'Fecha de registro'},
                'folio', {key: 'creado_por', label: 'Ingresado por'}, 
                'ISBN', {key: 'titulo', label: 'Libro'},
                {key: 'costo_unitario', label: 'Costo unitario'},
                'unidades', 'total'
            ],
            edit: false,
            pago_id: null,
            load: false
        }
    },
    created: function(){
        this.getTotales();
    },
    methods: {
        // OBTENER TOTALES
        getTotales(){
            this.editoriales.forEach(editorial => {
                this.eTotals.total += editorial.total;
                this.eTotals.total_pagos += editorial.total_pagos;
                this.eTotals.total_devolucion += editorial.total_devolucion;
                this.eTotals.total_pendiente += editorial.total_pendiente;
            });
        },
        // MOSTRAR LOS DEPOSITOS DE LA EDITORIAL
        verPagos(editorial){
            axios.get('/entradas/enteditoriale_pagos', {params: {id: editorial.id}}).then(response => {
                this.entrada.editorial = editorial.editorial;
                this.entrada.total_pagos = editorial.total_pagos;
                this.entrada.total_pendiente = editorial.total_pendiente;
                this.entrada.entdepositos = response.data.entdepositos;
                this.entrada.entdevoluciones = response.data.entdevoluciones;
                this.$refs['modal-mostrarPagos'].show();
            }).catch(error => {
                this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
            });
        },
        newEditorial(){
            this.$refs['modal-agregarEditorial'].show();
        },
        saveEditorial(){
            this.$bvModal.hide('modal-agregarEditorial');
            this.messageAlert('center', 'success', 'La editorial se guardo correctamente.', null, 'reload');
        }
    }
}
</script>

<style>

</style>