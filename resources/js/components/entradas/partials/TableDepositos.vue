<template>
    <div>
        <b-table :items="depositos" :fields="fields">
            <template v-slot:cell(index)="row">{{ row.index + 1}}</template>
            <template v-slot:cell(pago)="row">
                ${{ row.item.pago | formatNumber }}
            </template>
            <template v-if="role_id == 6" v-slot:cell(actions)="row">
                <b-button pill variant="warning" size="sm" @click="editPago(row.item)">
                    <i class="fa fa-pencil"></i>
                </b-button>
                <b-button pill variant="danger" size="sm" @click="deletePago(row.item)">
                    <i class="fa fa-close"></i>
                </b-button>
            </template>
            <template v-slot:cell(comprobante)="row">
                <a v-if="row.item.public_url.length > 0"
                    :href="row.item.public_url" target="_blank">
                    Ver
                </a>
            </template>
        </b-table>
        <b-modal ref="modal-registrarPago" title="Editar pago" 
            hide-footer>
            <new-edit-pago-entrada :form="form" :edit="true" @savedPago="savedPago"></new-edit-pago-entrada>
        </b-modal>
        <b-modal ref="modal-deletePago" title="Eliminar pago" hide-footer size="sm">
            <div class="text-center">
                <p>¿Estás seguro de eliminar el pago?</p>
                <b-button variant="danger" pill @click="confirmarEliminar()"
                    :disabled="load">
                    <i class="fa fa-close"></i> Confirmar
                </b-button>
            </div>
        </b-modal>
    </div>
</template>

<script>
import formatNumber from '../../../mixins/formatNumber';
import NewEditPagoEntrada from './../NewEditPagoEntrada.vue';
export default {
    props: ['depositos', 'role_id'],
    mixins: [formatNumber],
    components: {NewEditPagoEntrada},
    data(){
        return {
            load: false,
            fields: [
                {key: 'index', label: 'N.'},
                {key: 'created_at', label: 'Fecha de registro'},
                'pago',
                {key: 'ingresado_por', label: 'Ingresado por'}, 
                'nota',
                {key: 'fecha', label: 'Fecha del pago'},
                {key: 'comprobante', label: ''},
                {key: 'actions', label: ''},
            ],
            form: {
                id: null, 
                enteditoriale_id: null, 
                pago: null,
                fecha: null,
                nota: null,
                editorial: null,
                total_pendiente: null,
            },
        }
    },
    methods: {
        // EDITAR PAGO
        editPago(pago){
            // this.form = {
            //     id: pago.id, 
            //     enteditoriale_id: pago.enteditoriale_id, 
            //     pago: pago.pago,
            //     fecha: pago.fecha,
            //     nota: pago.nota,
            //     editorial: this.entrada.editorial,
            //     total_pendiente: this.entrada.total_pendiente,
            // };
            // this.$refs['modal-registrarPago'].show();
        },
        // PAGO GUARDADO
        savedPago(pago){
            // this.$refs['modal-registrarPago'].hide();
            // let msg = "El pago se guardo correctamente";
            // if(this.edit) msg = 'El pago se actualizo correctamente.';
            // mensaje de sweet
        },
        // ELIMINAR PAGO
        deletePago(pago){
            // this.pago_id = pago.id;
            // this.$refs['modal-deletePago'].show();
        },
        // CONFIRMAR ELIMINACION
        confirmarEliminar(){
            // this.load = true;
            // axios.delete('/entradas/delete_pago', {params: {pago_id: this.pago_id}}).then(response => {
            //     this.$refs['modal-deletePago'].hide();
            //     mensaje de sweet
            //     this.load = false;
            // }).catch(error => {
            //     this.load = false;
            //     this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
            // }); 
        },
    }
}
</script>

<style>

</style>