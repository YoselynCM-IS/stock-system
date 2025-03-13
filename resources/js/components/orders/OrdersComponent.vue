<template>
    <div>
        <div>
            <b-row class="mb-2">
                <b-col>
                    <pagination size="default" :limit="1" :data="pedidosData" 
                        @pagination-change-page="getResults">
                        <span slot="prev-nav"><i class="fa fa-angle-left"></i></span>
                        <span slot="next-nav"><i class="fa fa-angle-right"></i></span>
                    </pagination>
                </b-col>
                <b-col>
                    <label><b>Buscar por proveedor</b></label>
                    <b-form-select v-model="provider" :options="options" 
                        @change="http_byprovider()" :disabled="load">
                    </b-form-select>
                </b-col>
                <b-col>
                    <search-select-cliente-component :titulo="'Buscar por cliente'" :status="'all'" :load="load" :clientename="null" :tipo="null" @sendCliente="sendCliente"></search-select-cliente-component>
                </b-col>
                <b-col sm="2">
                    <b-button v-if="role_id == 1 || role_id == 2 || role_id == 6 || role_id == 10"
                        href="/order/create_edit/1/0" target="_blank" variant="success" pill :disabled="load">
                        <i class="fa fa-plus-circle"></i> Nuevo pedido
                    </b-button>
                </b-col>
            </b-row>
            <div v-if="!load">
                <b-table v-if="pedidos.length > 0" :items="pedidos" :fields="fieldsPedidos"
                    :busy="load">
                    <template v-slot:cell(index)="data">
                        {{ data.index + 1 }}
                    </template>
                    <template v-slot:cell(total_bill)="data">
                        ${{ data.item.total_bill | formatNumber }}
                    </template>
                    <template v-slot:cell(date)="data">
                        {{ data.item.date | moment }}
                    </template>
                    <template v-slot:cell(details)="data" pill>
                        <b-button variant="info" pill :disabled="load" size="sm"
                            :href="`/order/show/${data.item.id}`" target="blank">
                            <i class="fa fa-exclamation-circle"></i>
                        </b-button>
                        <b-button v-if="((role_id == 2 && data.item.actualizado_por == null) || role_id == 1 || role_id == 6) && data.item.status == 'iniciado'"
                            :href="`/order/create_edit/1/${data.item.id}`"
                            target="blank" variant="warning" pill size="sm">
                            <i class="fa fa-pencil"></i>
                        </b-button>
                    </template>
                    <template v-slot:cell(status)="data">
                        <estado-order :id="data.item.id" :status="data.item.status" :observations="data.item.observations"></estado-order>
                    </template>
                    <template #table-busy>
                        <div class="text-center text-danger my-2">
                        <b-spinner class="align-middle"></b-spinner>
                        <strong>Cargando...</strong>
                        </div>
                    </template>
                </b-table>
                <b-alert v-else show variant="secondary">
                    <i class="fa fa-exclamation-circle"></i> No se han agregado pedidos
                </b-alert>
            </div>
            <div v-else class="text-center text-info my-2 mt-3">
                <b-spinner class="align-middle"></b-spinner>
                <strong>Cargando...</strong>
            </div>
        </div>
    </div>
</template>

<script>
import EstadoOrder from './partials/EstadoOrder.vue';
import getEditoriales from '../../mixins/getEditoriales';
import formatNumber from '../../mixins/formatNumber';
import moment from '../../mixins/moment';
import SearchSelectClienteComponent from '../funciones/SearchSelectClienteComponent.vue';
    export default {
        components: { EstadoOrder, SearchSelectClienteComponent },
        mixins: [formatNumber, moment, getEditoriales],
        props: ['role_id'],
        data(){
            return {
                provider: null,
                pedidos: [],
                fieldsPedidos: [
                    {label: 'N.', key: 'index'},
                    {label: 'Fecha', key: 'date'},
                    {label: 'Identificador', key: 'identifier'},
                    {label: 'Proveedor', key: 'provider'},
                    {label: 'Para', key: 'destination'},
                    {label: 'Total Factura', key: 'total_bill'},
                    {label: 'Detalles', key: 'details'},
                    {label: '', key: 'status'}
                ],
                load: false,
                pedidosData: {},
                searchProveedor: false,
                cliente_id: null
            }
        },
        created: function(){
            this.getResults();
            this.get_editoriales();
        },
        methods: {
            getResults(page = 1){
                if(!this.searchProveedor && this.cliente_id == null){
                    this.http_pedidos(page);
                }
                if(this.searchProveedor){
                    this.http_byprovider(page);
                }
                if(this.cliente_id != null){
                    this.http_bycliente(page);
                }
            },
            http_pedidos(page){
                this.load = true;
                axios.get(`/order/index?page=${page}`).then(response => {
                    this.pedidosData = response.data; 
                    this.pedidos = response.data.data;
                    this.searchProveedor = false;
                    this.load = false;   
                }).catch(error => {
                    this.load = false;
                });
            },
            http_byprovider(page = 1){
                this.load = true;
                this.searchProveedor = true;
                this.cliente_id = null;
                axios.get(`/order/by_provider?page=${page}`, {params: {provider: this.provider}}).then(response => {
                    this.pedidos = response.data.data;
                    this.pedidosData = response.data;
                    this.load = false;
                }).catch(error => {
                    this.load = false;
                });
            },
            sendCliente(cliente){
                this.cliente_id = cliente.id;
                this.searchProveedor = false;
                this.provider = null;
                this.http_bycliente();
            },
            http_bycliente(page = 1){
                this.load = true;
                axios.get(`/order/by_cliente?page=${page}`, {params: {cliente_id: this.cliente_id}}).then(response => {
                    this.pedidosData = response.data; 
                    this.pedidos = response.data.data;
                    this.load = false;   
                }).catch(error => {
                    this.load = false;
                });
            }
        }
    }
</script>

<style>
    #listaL{
        position: absolute;
        z-index: 100
    }
</style>