<template>
    <div>
        <b-row>
            <b-col>
                <b-row>
                    <b-col sm="1"><label>Buscar</label></b-col>
                    <b-col sm="7">
                        <b-input v-model="queryCliente" @keyup="mostrarClientes()" style="text-transform:uppercase;"></b-input>
                        <div class="list-group" v-if="resultsClientes.length" id="listaL">
                            <a 
                                href="#" 
                                v-bind:key="i" 
                                class="list-group-item list-group-item-action" 
                                v-for="(result, i) in resultsClientes" 
                                @click="porCliente(result)">
                                {{ result.name }}
                            </a>
                        </div>
                    </b-col>
                </b-row>
            </b-col>
            <b-col sm="3">
                <b-button variant="dark" href="/administrador/download_unidades">
                    <i class="fa fa-download"></i> Descargar <br>
                    <i style="font-size: 12px;">Todo</i>
                </b-button>
            </b-col>
            <b-col sm="2" class="text-right">
                <b-button v-if="viewDetails" @click="viewDetails = !viewDetails" variant="dark">
                    <i class="fa fa-arrow-left"></i> Volver
                </b-button>
            </b-col>
        </b-row><br>
        <div v-if="!viewDetails">
            <b-table :items="clientes" :fields="fieldsClientes" :per-page="perPage" :current-page="currentPage">
                <template v-slot:cell(details)="row">
                    <b-button variant="info" v-on:click="showDetails(row.item)">Mostrar</b-button>
                </template>
            </b-table>
            <!-- PAGINACIÓN -->
            <b-pagination
                v-model="currentPage"
                :total-rows="clientes.length"
                :per-page="perPage"
            ></b-pagination>
        </div>
        <div v-else>
            <h6><b>Cliente: </b> {{ cliente.name }}</h6><br>
            <b-table :items="cliente.registros" :fields="fieldsDetails">
                <template v-slot:cell(index)="row">{{ row.index + 1 }}</template>
                <template #thead-top="row">
                    <tr>
                        <th colspan="2"></th>
                        <th>{{ cliente.unidades_vendidas }}</th>
                        <th>{{ cliente.unidades_remisiones }}</th>
                        <th>{{ cliente.unidades_devoluciones }}</th>
                    </tr>
                </template>
            </b-table>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                clientes: [],
                fieldsClientes: [
                    'cliente',
                    { key: 'unidades_vendidas', label: 'Unidades (Vendidas)', variant: 'success', sortable: true },
                    { key: 'unidades_remisiones', label: 'Unidades (Salida)' },
                    { key: 'unidades_devoluciones', label: 'Unidades (Devoluciones)' },
                    { key: 'details', label: 'Detalles' }
                ],
                fieldsDetails: [
                    { key: 'index', label: '' }, 'libro',
                    { key: 'unidades_vendidas', label: 'Unidades (Vendidas)', variant: 'success' },
                    { key: 'unidades_remisiones', label: 'Unidades (Salida)' },
                    { key: 'unidades_devoluciones', label: 'Unidades (Devoluciones)' }
                ],
                cliente: {
                    name: '',
                    unidades_vendidas: 0,
                    unidades_remisiones: 0,
                    unidades_devoluciones: 0,
                    registros: []
                },
                viewDetails: false,
                queryCliente: '',
                resultsClientes: [],
                perPage: 10,
                currentPage: 1,
            }
        },
        created: function(){
            axios.get('/administrador/getUnidades').then(response => {
                this.clientes = response.data;
            }); 
        },
        methods: {
            mostrarClientes(){
                if(this.queryCliente.length > 0){
                    axios.get('/mostrarClientes', {params: {queryCliente: this.queryCliente}}).then(response => {
                        this.resultsClientes = response.data;
                    }); 
                } else{ this.resultsClientes = []; }
            },
            porCliente(cliente){
                axios.get('/administrador/detallesUnidades', {params: {cliente_id: cliente.id}}).then(response => {
                    if(response.data.length > 0){
                        this.cliente.name = cliente.name;
                        this.cliente.registros = response.data;
                        this.viewDetails = true;
                    } else {
                        this.$bvToast.toast(`${cliente.name} no cuenta con registro de remisiones`, {
                            title: 'Mensaje',
                            variant: 'warning',
                            solid: true
                        });
                    }
                    this.queryCliente = '';
                    this.resultsClientes = [];
                });
            },
            // MOSTRAR LOS DETALLES
            showDetails(cliente){
                axios.get('/administrador/detallesUnidades', {params: {cliente_id: cliente.cliente_id}}).then(response => {
                    this.cliente.name = cliente.cliente;
                    this.cliente.unidades_vendidas = cliente.unidades_vendidas;
                    this.cliente.unidades_remisiones = cliente.unidades_remisiones;
                    this.cliente.unidades_devoluciones = cliente.unidades_devoluciones;
                    this.cliente.registros = response.data;
                    this.viewDetails = true;
                });
            }
        }
    }
</script>