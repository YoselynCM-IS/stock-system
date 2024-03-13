<template>
    <div>
        <b-row>
            <b-col>
                <h5><b>DETALLES DEL PEDIDO</b></h5>
            </b-col>
            <b-col sm="2">
                <estado-pedido :id="pedido.id" :comentarios="pedido.comentarios" :estado="pedido.estado"></estado-pedido>
            </b-col>
            <b-col sm="2">
                <b-button v-if="(role_id == 5|| role_id == 9 || role_id == 6 || role_id == 7) && pedido.estado == 'proceso'" 
                    :disabled="load"
                    variant="danger" pill block @click="cancelarPedido()">
                    <i class="fa fa-close"></i> Cancelar
                </b-button>
            </b-col>
            <b-col sm="2">
                <b-button v-if="(role_id == 2 || role_id == 6) && pedido.estado == 'proceso'"
                    :href="`/pedido/preparar/${pedido.id}`" 
                    variant="dark" pill block :disabled="load">
                    Preparar pedido
                </b-button>
                <b-button v-if="pedido.estado == 'en orden'" @click="seguimiento()"
                    variant="dark" pill block :disabled="load">
                    Seguimiento
                </b-button>
            </b-col>
        </b-row>
        <hr>
        <datos-pedido :cliente_name="pedido.cliente.name" 
                    :user_name="pedido.user.name" 
                    :created_at="pedido.created_at">
        </datos-pedido>
        <b-table :items="pedido.peticiones" :fields="fields">
            <template v-slot:cell(index)="row">
                {{ row.index + 1 }}
            </template>
            <template v-slot:cell(quantity)="row">
                {{ row.item.quantity | formatNumber }}
            </template>
            <template v-slot:cell(price)="row">
                ${{ row.item.price | formatNumber }}
            </template>
            <template v-slot:cell(total)="row">
                ${{ row.item.total | formatNumber }}
            </template>
            <template #thead-top="row">
                <tr>
                    <th colspan="4"></th>
                    <th>{{ pedido.total_quantity | formatNumber }}</th>
                    <th></th>
                    <th>${{ pedido.total | formatNumber }}</th>
                </tr>
            </template>
        </b-table>
        <!-- MODALS -->
        <b-modal id="modal-Seguimiento" title="Seguimiento del pedido" size="lg" hide-footer>
            <b-card v-for="(order, i) in pedido.orders" v-bind:key="i"
                border-variant="light" header="Se preparo el pedido para el proveedor">
                <b-row>
                    <b-col><a :href="`/order/show/${order.id}`" target="_blank" class="card-link">{{ order.identifier }} / {{ order.provider }}</a></b-col>
                    <b-col sm="3">{{ order.date }}</b-col>
                </b-row>
                <div v-if="order.remisiones.length > 0">
                    <hr>
                    Salida de remision(es)
                    <b-list-group flush class="mb-2">
                        <b-list-group-item v-for="(remisione, i) in order.remisiones" v-bind:key="i">
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
                </div>
            </b-card>
        </b-modal>
    </div>
</template>

<script>
import DatosPedido from './partials/DatosPedido.vue'
import EstadoPedido from './partials/EstadoPedido.vue';
import formatNumber from '../../mixins/formatNumber';
import sweetAlert from '../../mixins/sweetAlert';
export default {
    components: { DatosPedido, EstadoPedido },
    mixins: [formatNumber, sweetAlert],
    props: ['pedido', 'role_id'],
    data(){
        return {
            fields: [
                {key: 'index', label: 'N.'},
                {key: 'libro.ISBN', label: 'ISBN'},
                {key: 'libro.titulo', label: 'Titulo'},
                {key: 'tipo', label: ''},
                {key: 'quantity', label: 'Unidades'},
                {key: 'price', label: 'Precio'},
                {key: 'total', label: 'Total'}
            ],
            load: false
        }
    },
    methods: {
        cancelarPedido(){
            this.load = true;
            let form = {pedido_id: this.pedido.id };
            axios.put('/pedido/cancelar', form).then(response => {
                this.messageAlert('center', 'warning', 'El pedido se ha cancelado.', null, 'reload');
                this.load = false;
            }).catch(error => {
                this.load = false;
            });
        },
        seguimiento(){
            this.$bvModal.show('modal-Seguimiento');
        }
    }
}
</script>

<style>

</style>