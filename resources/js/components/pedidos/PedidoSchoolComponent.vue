<template>
    <div>
        <div v-if="!openPedido">
            <b-row class="mb-3">
                <b-col>
                    <!-- PAGINACIÓN -->
                    <pagination size="default" :limit="1" :data="pedidos" 
                        @pagination-change-page="getResults">
                        <span slot="prev-nav"><i class="fa fa-angle-left"></i></span>
                        <span slot="next-nav"><i class="fa fa-angle-right"></i></span>
                    </pagination>
                </b-col>
                <b-col>
                    <search-select-cliente-component :load="load" @sendCliente="sendCliente"></search-select-cliente-component>
                </b-col>
                <b-col sm="2" class="text-right">
                    <b-button v-if="role_id == 5 || role_id == 6 || role_id == 7"
                        variant="success" pill @click="newPedido()" 
                        :disabled="load">
                        <i class="fa fa-plus-circle"></i> Nuevo pedido
                    </b-button>
                </b-col>
            </b-row>
            <div v-if="!load">
                <b-table v-if="pedidos.data.length"
                    class="mt-2" :items="pedidos.data" :fields="fields"
                    :tbody-tr-class="rowClass">
                    <template v-slot:cell(index)="row">
                        {{ row.index + 1 }}
                    </template>
                    <template v-slot:cell(total_quantity)="row">
                        {{ row.item.total_quantity |formatNumber }}
                    </template>
                    <template v-slot:cell(total)="row">
                        ${{ row.item.total |formatNumber }}
                    </template>
                    <template v-slot:cell(estado)="row">
                        <estado-pedido :id="row.item.id" :comentarios="row.item.comentarios" :estado="row.item.estado"></estado-pedido>
                    </template>
                    <template v-slot:cell(details)="row">
                        <b-button :href="`/pedido/show/${row.item.id}`" 
                            target="blank" variant="info" pill size="sm">
                            <i class="fa fa-info-circle"></i>
                        </b-button>
                    </template>
                </b-table>
                <no-registros-component v-else></no-registros-component>
            </div>
            <load-component v-else></load-component>
        </div>
        <div v-if="openPedido">
            <b-row>
                <b-col sm="10">
                    <h4><b>Nuevo pedido</b></h4>
                </b-col>
                <b-col>
                    <b-button pill block variant="secondary" @click="openPedido = false" 
                        :disabled="load">
                        <i class="fa fa-reply"></i> Volver
                    </b-button>
                </b-col>
            </b-row><hr>
            <new-pedido-component></new-pedido-component>
        </div>
    </div>
</template>

<script>
import SearchSelectClienteComponent from '../funciones/SearchSelectClienteComponent.vue';
import NewPedidoComponent from './NewPedidoComponent.vue';
import EstadoPedido from './partials/EstadoPedido.vue';
import formatNumber from '../../mixins/formatNumber';
export default {
    props: ['role_id'],
    components: { NewPedidoComponent, EstadoPedido, SearchSelectClienteComponent },
    mixins: [formatNumber],
    data(){
        return {
            load: false,
            openPedido: false,
            pedidos: {},
            fields: [
                {key: 'index', label: 'N.'},
                {key: 'cliente.name', label: 'Cliente'},
                {key: 'total_quantity', label: 'Unidades'},
                {key: 'total', label: 'Total'},
                {key: 'user.name', label: 'Creado por'},
                {key: 'created_at', label: 'Creado el'},
                {key: 'details', label: 'Detalles'},
                {key: 'estado', label: 'Estado'}
            ],
            cliente_id: null
        }
    },
    created: function(){
        this.getResults();
    },
    methods: {
        getResults(page = 1){
            if(this.cliente_id == null) this.http_pedidos(page);
            else this.http_bycliente(page);
        },
        http_pedidos(page = 1){
            this.load = true;
            axios.get(`/pedido/index?page=${page}`).then(response => {
                this.pedidos = response.data;
                this.load = false;
            }).catch(error => {
                this.load = true;
            });
        },
        newPedido(){
            // this.editar = false;
            this.openPedido = true;
        },
        rowClass(item, type){
            if (!item) return
            if (item.estado == 'cancelado') return 'table-danger';
            if (item.estado == 'de inventario') return 'table-success';
            if (item.estado == 'en orden') return 'table-primary';
        },
        sendCliente(cliente){
            this.cliente_id = cliente.id;
            this.http_bycliente();
        },
        http_bycliente(page = 1){
            this.load = true;
            axios.get(`/pedido/by_cliente?page=${page}`, {params: {cliente_id: this.cliente_id}}).then(response => {
                this.pedidos = response.data;
                this.load = false;
            }).catch(error => {
                this.load = true;
            });
        }
    }
}
</script>

<style>

</style>