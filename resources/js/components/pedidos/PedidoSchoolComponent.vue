<template>
    <div>
        <div>
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
                    <search-select-cliente-component :load="load" :status="'all'" :clientename="null" :tipo="null" @sendCliente="sendCliente"></search-select-cliente-component>
                </b-col>
                <b-col sm="2" class="text-right">
                    <b-button v-if="role_id == 1 || role_id == 5 || role_id == 6 || role_id == 9 || role_id == 10"
                        href="/pedido/create_edit/1/0" target="blank" variant="success" pill :disabled="load">
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
                    <template v-slot:cell(created_at)="row">
                        {{ row.item.created_at | momentDet }}
                    </template>
                    <template v-slot:cell(actions)="row">
                        <b-button :href="`/pedido/show/${row.item.id}`" 
                            target="blank" variant="info" pill size="sm">
                            <i class="fa fa-info-circle"></i>
                        </b-button>
                        <b-button v-if="row.item.estado == 'proceso' && (((role_id == 2 || role_id == 5 || role_id == 9 || role_id == 10) && row.item.actualizado_por == null) || role_id == 1 || role_id == 6)"
                            :href="`/pedido/create_edit/1/${row.item.id}`"
                            target="blank" variant="warning" pill size="sm">
                            <i class="fa fa-pencil"></i>
                        </b-button>
                        <b-button v-if="(role_id == 1 || role_id == 2 || role_id == 6 || role_id == 10) && row.item.cerrado_por == null && (row.item.estado == 'de inventario' || row.item.estado == 'en orden')"
                            @click="cerrarPedido(row.item.id)" variant="dark" pill size="sm">
                            <i class="fa fa-close"></i>
                        </b-button>
                    </template>
                </b-table>
                <no-registros-component v-else></no-registros-component>
            </div>
            <load-component v-else></load-component>
        </div>
    </div>
</template>

<script>
import SearchSelectClienteComponent from '../funciones/SearchSelectClienteComponent.vue';
import EstadoPedido from './partials/EstadoPedido.vue';
import formatNumber from '../../mixins/formatNumber';
import moment from '../../mixins/moment';
import sweetAlert from '../../mixins/sweetAlert';
export default {
    props: ['role_id'],
    components: { EstadoPedido, SearchSelectClienteComponent },
    mixins: [formatNumber, moment, sweetAlert],
    data(){
        return {
            load: false,
            pedidos: {},
            fields: [
                {key: 'index', label: 'N.'},
                {key: 'cliente.name', label: 'Cliente'},
                {key: 'total_quantity', label: 'Unidades'},
                {key: 'total', label: 'Total'},
                {key: 'user.name', label: 'Creado por'},
                {key: 'created_at', label: 'Creado el'},
                {key: 'estado', label: 'Estado'},
                {key: 'actions', label: ''},
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
        rowClass(item, type){
            if (!item) return
            if (item.estado == 'cancelado') return 'table-danger';
            if (item.cerrado_por == null && (item.estado == 'en orden' || item.estado == 'de inventario')) return 'table-primary';
            if (item.cerrado_por !== null) return 'table-success';
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
        },
        // CERRAR EL PEDIDO PARA QUE YA NO SE HAGN MOVIMIENTOS
        cerrarPedido(pedido_id){
            this.load = true;
            let form = {pedido_id: pedido_id};
            axios.put('/pedido/cerrar', form).then(response => {
                this.messageAlert('center', 'success', 'El pedido se ha cerrado.', null, 'reload');
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