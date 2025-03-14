<template>
    <div>
        <div v-if="!openCostos">
            <b-row>
                <b-col sm="4">
                    <h5><b>Pedido:</b> {{ pedido.identifier }}</h5>
                    <a v-if="pedido.pedido_id > 0" :href="`/pedido/show/${pedido.pedido_id}`" target="_blank">
                        <strong><i>Pedido del cliente</i></strong>
                    </a>
                </b-col>
                <b-col sm="2">
                    <b-button v-if="(pedido.status == 'iniciado' && pedido.total_bill > 0) && (role_id === 1 || role_id == 2 || role_id == 6 || role_id == 10)" 
                        @click="sendPedido()" pill block variant="dark">
                        <i class="fa fa-send"></i> {{ pedido.almacen == 'SI' ? 'Enviar':'Preparar' }}
                    </b-button>
                    <estado-order v-else :id="pedido.id" :status="pedido.status" :observations="pedido.observations"></estado-order>
                </b-col>
                <b-col sm="2">
                    <b-button v-if="pedido.status == 'espera' && (role_id === 1 || role_id == 2 || role_id == 6 || role_id == 10)" variant="danger"
                        pill :disabled="load" @click="openCancelar = true">
                        <i class="fa fa-close"></i> Cancelar
                    </b-button>
                </b-col>
                <b-col sm="2">
                    <b-button v-if="(pedido.status == 'iniciado' && pedido.total_bill == 0) && (role_id === 1 || role_id == 2 || role_id == 6 || role_id == 10)" 
                        @click="addCostos()" pill variant="success" block>
                        <i class="fa fa-dollar"></i> Agregar costos
                    </b-button>
                </b-col>
                <b-col sm="2">
                    <div v-if="(pedido.total_bill > 0) && (pedido.status == 'espera')">
                        <b-button v-if="(role_id == 2 && pedido.almacen == 'NO') || (role_id == 1 || role_id == 3 || role_id == 6 || role_id == 10)" variant="primary" 
                            pill @click="act_status()" :disabled="load">
                            <i class="fa fa-refresh"></i> Actualizar
                        </b-button>
                    </div>
                    <div v-if="(role_id == 1 || role_id == 2 || role_id == 6 || role_id == 10) && 
                        (pedido.pedido_id > 0 && (pedido.status == 'incompleto' || pedido.status == 'completo'))">
                        <b-button v-if="pedido.remisiones_count == 0" 
                            variant="dark" pill :disabled="load" @click="relacionarRems()">
                            <i class="fa fa-exchange"></i> Relacionar
                        </b-button>
                        <b-button v-else variant="dark" pill :disabled="load" @click="getRemisiones()">
                            <i class="fa fa-list"></i> Remisiones
                        </b-button>
                    </div>
                </b-col>
            </b-row>
            <datos-order :order="pedido"></datos-order>
            <b-table :items="pedido.elements" :fields="pedido.status == 'incompleto' ? fieldsElem2:fieldsElem1">
                <template v-slot:cell(index)="data">
                    {{ data.index + 1 }}
                </template>
                <template v-slot:cell(quantity)="data">
                    {{ data.item.quantity | formatNumber }}
                </template>
                <template v-slot:cell(actual_quantity)="data">
                    {{ data.item.actual_quantity | formatNumber }}
                </template>
                <template v-slot:cell(unit_price)="data">
                    ${{ data.item.unit_price | formatNumber }}
                </template>
                <template v-slot:cell(total)="data">
                    ${{ data.item.total | formatNumber }}
                </template>
                <template v-slot:cell(actual_total)="data">
                    ${{ data.item.actual_total | formatNumber }}
                </template>
                <template #thead-top="row">
                    <tr class="mt-5">
                        <th colspan="5"></th>
                        <th class="text-right"><b>Total Factura</b></th>
                        <th>
                            <b>${{ pedido.total_bill | formatNumber }}</b>
                        </th>
                        <th></th>
                    </tr>
                </template>
            </b-table>
            <b-modal v-model="openStatus" title="Actualizar estado del pedido"
                hide-footer size="xl">
                <label><b>Estado del pedido</b></label>
                <b-form-select v-model="pedidoStatus.status" :options="estados" :disabled="load"></b-form-select>
                <label><b>Observaciones</b></label>
                <b-form-textarea v-model="pedidoStatus.observations"
                    rows="3" max-rows="6" :disabled="load" placeholder="Opcional"
                ></b-form-textarea>  
                <div v-if="pedidoStatus.status == 'incompleto'" class="mt-3">
                    <b-table :items="pedidoStatus.elements" :fields="fieldsElements">
                        <template v-slot:cell(index)="data">
                            {{ data.index + 1 }}
                        </template>
                        <template v-slot:cell(actual_quantity)="data">
                            <b-input required type="number" v-model="data.item.actual_quantity" :disabled="load"
                                    @change="updateTotal(data.item, data.index)"></b-input>
                        </template>
                        <template #thead-top="row">
                            <tr class="mt-5">
                                <th colspan="4"></th>
                                <th>
                                    <b>{{ pedidoStatus.total_quantity | formatNumber }}</b>
                                </th>
                            </tr>
                        </template>
                    </b-table>
                </div>
                <div class="text-right mt-2">
                    <b-button :disabled="pedidoStatus.status == null || load || (pedidoStatus.status == 'incompleto' && pedidoStatus.total_quantity == 0)" 
                        variant="success" @click="change_status()" pill>
                        <i class="fa fa-check-circle"></i> Actualizar estado
                    </b-button>
                </div>
            </b-modal>
            <b-modal v-model="openCancelar" title="Cancelar pedido" hide-footer>
                <b-alert show variant="danger">
                    <i class="fa fa-exclamation-triangle"></i> 
                    ¿Estás seguro de cancelar el pedido?, una vez realizada esta acción no se podrá deshacer.
                </b-alert>
                <div class="text-right">
                    <b-button pill variant="dark" @click="cancelar_pedido()">Confimar</b-button>
                </div>
            </b-modal>
        </div>
        <div v-if="openCostos">
            <b-row>
                <b-col><h5><b>Pedido</b> {{ pedido.identifier }}</h5></b-col>
                <b-col sm="2">
                    <b-button variant="secondary" @click="(openCostos = false)"
                        pill block :disabled="load">
                        <i class="fa fa-arrow-circle-left"></i> Volver
                    </b-button>
                </b-col>
            </b-row>
            <add-costos-order-component :order="pedido"></add-costos-order-component>
        </div>
        <!-- MODASL -->
        <!-- AGREGAR ACTIVIDAD -->
        <b-modal id="modal-relacionarRemd" title="Relacionar remisiones" hide-footer size="xl">
            <relacionar-remisiones :order_id="pedido.id"></relacionar-remisiones>
        </b-modal>
        <b-modal id="modal-remisiones" title="Remisiones" hide-footer>
            <b-list-group flush class="mb-2">
                <b-list-group-item v-for="(remisione, i) in pedido.remisiones" v-bind:key="i">
                    <b-row>
                        <b-col>
                            <a :href="`/remisiones/details/${remisione.id}`" target="_blank" class="card-link">
                                {{ remisione.id }} / {{ remisione.cliente.name }}
                            </a>
                        </b-col>
                        <b-col sm="3">{{ remisione.fecha_creacion }}</b-col>
                    </b-row>
                </b-list-group-item>
            </b-list-group>
        </b-modal>
    </div>
</template>

<script>
import toast from '../../mixins/toast';
import formatNumber from '../../mixins/formatNumber';
import moment from '../../mixins/moment';
import DatosOrder from './partials/DatosOrder.vue';
import EstadoOrder from './partials/EstadoOrder.vue';
import RelacionarRemisiones from './RelacionarRemisiones.vue';
import sweetAlert from '../../mixins/sweetAlert';
export default {
    components: { DatosOrder, EstadoOrder, RelacionarRemisiones },
    props: ['pedido', 'role_id'],
    mixins: [formatNumber, moment, toast, sweetAlert],
    data(){
        return {
            load: false,
            openCostos: false,
            openCancelar: false,
            fieldsElem1: [
                {label: 'N.', key: 'index'},
                {label: 'ISBN', key: 'libro.ISBN'},
                {label: 'Titulo', key: 'libro.titulo'},
                {label: '', key: 'tipo'},
                {label: 'Cantidad', key: 'quantity'},
                {label: 'Precio unitario', key: 'unit_price'},
                {label: 'Total', key: 'total'}
            ],
            fieldsElem2: [
                {label: 'N.', key: 'index'},
                {label: 'ISBN', key: 'libro.ISBN'},
                {label: 'Titulo', key: 'libro.titulo'},
                {label: '', key: 'tipo'},
                {label: 'Cantidad', key: 'quantity'},
                {label: 'Precio unitario', key: 'unit_price'},
                {label: 'Total', key: 'total'},
                {label: 'Cantidad (recibida)', key: 'actual_quantity'},
                {label: 'Total (recibido)', key: 'actual_total'},
            ],
            openStatus: false,
            pedidoStatus: {
                pedido_id: null, status: null, observations: '', elements: [], total_quantity: 0
            },
            estados: [
                { value: null, text: 'Selecciona una opción', disabled: true },
                { value: 'rechazado', text: 'Rechazado' },
                { value: 'completo', text: 'Recibido (Completo)' },
                { value: 'incompleto', text: 'Recibido (Incompleto)' }
            ],
            fieldsElements: [
                {label: 'N.', key: 'index'},
                {label: 'ISBN', key: 'libro.ISBN'},
                {label: 'Titulo', key: 'libro.titulo'},
                {label: '', key: 'tipo'},
                {label: 'Cantidad', key: 'actual_quantity'}
            ]
        }
    },
    methods: {
        addCostos(){
            this.openCostos = true;
        },
        cancelar_pedido(){
            this.load = true;
            axios.put('/order/cancelar', this.pedido).then(response => {
                this.messageAlert('center', 'warning', 'El pedido ha sido cancelado', null, 'reload');
                this.load = false;
            }).catch(error => {
                this.load = false;
            });
        },
        act_status(){
            this.openStatus = true;
            this.pedidoStatus = { pedido_id: null, status: null, observations: '', elements: [], total_quantity: 0 };
            this.pedidoStatus.pedido_id = this.pedido.id;
            this.pedido.elements.forEach(element => {
                this.pedidoStatus.elements.push({
                    id: element.id,
                    order_id: element.order_id,
                    libro_id: element.libro_id,
                    tipo: element.tipo,
                    libro: {
                        ISBN: element.libro.ISBN,
                        titulo: element.libro.titulo
                    },
                    actual_quantity: 0
                });
            });
        },
        change_status(){
            this.load = true;
            axios.put('/order/change_status', this.pedidoStatus).then(response => {
                this.messageAlert('center', 'success', 'El estado del pedido se actualizo correctamente', null, 'reload');
                this.load = false;
            }).catch(error => {
                this.load = false;
            });
        },
        relacionarRems(){
            this.$bvModal.show('modal-relacionarRemd');
        },
        sendPedido(){
            this.load = true;
            let form = { pedido_id: this.pedido.id, status: 'espera' };
            axios.put('/order/change_status', form).then(response => {
                this.messageAlert('center', 'success', 'El pedido se envió/preparó correctamente.', null, 'reload');
                this.load = false;
            }).catch(error => {
                this.load = false;
            });
        },
        updateTotal(element, position){
            if(element.actual_quantity > 0){
                this.pedidoStatus.total_quantity = 0;
                this.pedidoStatus.elements.forEach(e => {
                    this.pedidoStatus.total_quantity += e.actual_quantity;
                });
            } else {
                this.pedidoStatus.elements[position].actual_quantity = 0;
                this.makeToast('warning', 'Las unidades deben ser mayor a 0.');
            }
        },
        getRemisiones(){
            this.$bvModal.show('modal-remisiones');
        }
    }
}
</script>

<style>

</style>