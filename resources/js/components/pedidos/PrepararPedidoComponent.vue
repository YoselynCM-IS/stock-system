<template>
    <div>
        <b-row>
            <b-col>
                <h5><b>PREPARAR PEDIDO</b></h5>
            </b-col>
            <b-col sm="3">
                <b-button v-if="(total_faltante == 0)"
                    variant="primary" pill block @click="tomarInventario()"
                    :disabled="load">
                    <i class="fa fa-check-square-o"></i> Disponible en inventario
                </b-button>
            </b-col>
            <b-col sm="2">
                <b-button variant="success" pill block @click="savePreparacion()"
                    :disabled="(load || total_solicitar == 0)">
                    <i class="fa fa-check"></i> Guardar
                </b-button>
            </b-col>
        </b-row>
        <hr>
        <datos-pedido :cliente_name="pedido.cliente_name" 
                    :user_name="pedido.user_name" 
                    :created_at="pedido.created_at">
        </datos-pedido>
        <b-table :items="pedido.peticiones" :fields="fields">
            <template v-slot:cell(index)="row">
                {{ row.index + 1 }}
            </template>
            <template v-slot:cell(solicitar)="row">
                <b-input :id="`inpPed-${row.index}`" type="number" 
                    v-model="row.item.solicitar" :disabled="load"
                    @change="guardarSolicitar(row.item, row.index)"/>
            </template>
            <template v-slot:cell(quantity)="row">
                {{ row.item.quantity | formatNumber }}
            </template>
            <template v-slot:cell(existencia)="row">
                {{ row.item.existencia | formatNumber }}
            </template>
            <template v-slot:cell(faltante)="row">
                {{ row.item.faltante | formatNumber }}
            </template>
            <template #thead-top="row">
                <b-tr>
                    <b-th colspan="4"></b-th>
                    <b-th colspan="3" class="text-center" variant="info">Unidades</b-th><b-th></b-th>
                </b-tr>
                <b-tr>
                    <b-th colspan="4"></b-th>
                    <b-th>{{ pedido.total_quantity | formatNumber }}</b-th>
                    <b-th colspan="2"></b-th>
                    <b-th>{{ total_solicitar | formatNumber }}</b-th>
                </b-tr>
            </template>
        </b-table>
    </div>
</template>

<script>
import DatosPedido from './partials/DatosPedido.vue'
import toast from '../../mixins/toast';
import formatNumber from '../../mixins/formatNumber';
import sweetAlert from '../../mixins/sweetAlert';
export default {
    components: { DatosPedido },
    mixins: [toast, formatNumber, sweetAlert],
    props: ['pedido'],
    data(){
        return {
            fields: [
                {key: 'index', label: 'N.'},
                {key: 'ISBN', label: 'ISBN'},
                {key: 'titulo', label: 'Titulo'},
                {key: 'tipo', label: ''},
                {key: 'quantity', label: 'Solicitadas'},
                {key: 'existencia', label: 'En existencia'},
                {key: 'faltante', label: 'Faltantes'},
                {key: 'solicitar', label: 'Solicitar'}
            ],
            load: false,
            total_solicitar: 0,
            total_faltante: 0,
        }
    },
    created: function(){
        this.pedido.peticiones.forEach(peticione => {
            this.total_faltante += peticione.faltante;
        });
    },
    methods: {
        guardarSolicitar(peticione, index){
            if(peticione.solicitar > 0){
                if(peticione.solicitar >= peticione.faltante){
                    if(index + 1 < this.pedido.peticiones.length){
                        document.getElementById('inpPed-'+(index+1)).focus();
                        document.getElementById('inpPed-'+(index+1)).select();
                    }
                } else {
                    this.pedido.peticiones[index].solicitar = 0;
                    this.makeToast('warning', 'Las unidades para solicitar deben ser igual o mayor a las unidades faltantes.');
                }
            } else {
                this.pedido.peticiones[index].solicitar = 0;
                this.makeToast('warning', 'Las unidades deben ser mayor a 0.');
            }
            this.total_solicitar = 0;
            this.pedido.peticiones.forEach(p => {
                this.total_solicitar += parseInt(p.solicitar);
            });
        },
        savePreparacion(){
            if(this.check_peticiones() == 0){
                this.load = true;
                this.pedido.total_solicitar = this.total_solicitar;
                axios.put(`/pedido/preparado`, this.pedido).then(response => {
                    this.messageAlert('center', 'success', 'El pedido para el proveedor se creo correctamento.', null, 'close');
                    this.load = false;
                }).catch(error => {
                    this.load = false;
                });
            } else {
                this.makeToast('warning', 'Las unidades para solicitar deben ser igual o mayor a las unidades faltantes.');
            }
        },
        check_peticiones(){
            let count_check = 0;
            this.pedido.peticiones.forEach(peticione => {
                if(peticione.solicitar < peticione.faltante) count_check++;
            });
            return count_check;
        },
        tomarInventario(){
            this.load = true;
            axios.put(`/pedido/despachar`, this.pedido).then(response => {
                this.messageAlert('center', 'success', 'El pedido se tomará de lo disponible en inventario.', null, 'close');
                this.load = false;
            }).catch(error => {
                this.load = false;
            });
        }
    }
}
</script>

<style>

</style>