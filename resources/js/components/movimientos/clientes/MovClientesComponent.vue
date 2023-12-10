<template>
    <div>
        <div v-if="!load">
            <!-- FUNCIONES (ENCABEZADO) -->
            <b-row>
                <b-col>
                    <b-pagination v-model="currentPage" :per-page="perPage"
                        :total-rows="remclientes.length" aria-controls="table-mov">
                    </b-pagination>
                </b-col>
                <b-col>
                    <!-- BUSCAR CLIENTE -->
                    <b-input v-model="queryCliente" @keyup="mostrarClientes()"
                        style="text-transform:uppercase;" placeholder="BUSCAR CLIENTE">
                    </b-input>
                    <div class="list-group" v-if="clientes.length" id="listP">
                        <a href="#" v-bind:key="i" class="list-group-item list-group-item-action" 
                            v-for="(cliente, i) in clientes" @click="selectCliente(cliente)">
                            {{ cliente.name }}
                        </a>
                    </div>
                </b-col>
            </b-row>
            <!-- TABLA DE REMCLIENTES -->
            <b-table :items="remclientes" :fields="fieldsRClientes"
                    :per-page="perPage" :current-page="currentPage">
                <template v-slot:cell(index)="row">{{ row.index + 1 }}</template>
                <template v-slot:cell(total)="row">${{ row.item.total | formatNumber }}</template>
                <template v-slot:cell(total_devolucion)="row">${{ row.item.total_devolucion | formatNumber }}</template>
                <template v-slot:cell(total_pagar)="row">${{ row.item.total_pagar | formatNumber }}</template>
                <template v-slot:cell(total_pagos)="row">${{ row.item.total_pagos | formatNumber }}</template>
                <template #row-details="row">
                    <b-card>
                        <b-list-group>
                            <b-list-group-item v-bind:key="i" 
                                    v-for="(tipo, i) in row.item.by_cortes">
                                    <h6><strong>{{ tipo.corte }}</strong></h6>
                                    <ul class="col-md-6">
                                        <li>
                                            <b-row>
                                                <b-col sm="1">
                                                    <b-badge :variant="`${tipo.cta ? 'success':'danger'}`">
                                                        <i v-if="tipo.cta" class="fa fa-check"></i>
                                                        <i v-else class="fa fa-close"></i>
                                                    </b-badge>
                                                </b-col>
                                                <b-col sm="4"><b>TOTAL:</b></b-col>
                                                <b-col>${{ tipo.total | formatNumber }}</b-col>
                                            </b-row>
                                        </li>
                                        <li>
                                            <b-row>
                                                <b-col sm="1">
                                                    <b-badge :variant="`${tipo.ctp ? 'success':'danger'}`">
                                                        <i v-if="tipo.ctp" class="fa fa-check"></i>
                                                        <i v-else class="fa fa-close"></i>
                                                    </b-badge>
                                                </b-col>
                                                <b-col sm="4"><b>PAGOS:</b></b-col>
                                                <b-col>${{ tipo.total_pagos | formatNumber }}</b-col>
                                            </b-row>
                                        </li>
                                        <li>
                                            <b-row>
                                                <b-col sm="1">
                                                    <b-badge :variant="`${tipo.ctd ? 'success':'danger'}`">
                                                        <i v-if="tipo.ctd" class="fa fa-check"></i>
                                                        <i v-else class="fa fa-close"></i>
                                                    </b-badge>
                                                </b-col>
                                                <b-col sm="4"><b>DEVOLUCIÓN:</b></b-col>
                                                <b-col>${{ tipo.total_devolucion | formatNumber }}</b-col>
                                            </b-row>
                                        </li>
                                    </ul>
                            </b-list-group-item>
                        </b-list-group>
                    </b-card>
                </template>
            </b-table>
        </div>
        <load-component v-else></load-component>
    </div>
</template>

<script>
import formatNumber from '../../../mixins/formatNumber';
import searchCliente from '../../../mixins/searchCliente';
import LoadComponent from '../../cortes/partials/LoadComponent.vue';
export default {
    components: { LoadComponent },
    mixins: [formatNumber, searchCliente],
    data(){
        return {
            load: false,
            remclientes: [],
            fieldsRClientes: [
                {key: 'index', label: 'N.'},
                {key: 'name', label: 'Cliente'}, 
                {key: 'total', label: 'Salida'}, 
                {key: 'total_pagos', label: 'Pagado'},
                {key: 'total_devolucion', label: 'Devolución'}, 
                {key: 'total_pagar', label: 'Pagar'}
            ],
            perPage: 20,
            currentPage: 1,
        }
    },
    created: function(){
        this.getResults();
    },
    methods: {
        // OBTENER LISTA DE REMCLIENTES
        getResults(){
            this.load = true;
            axios.get('/remcliente/get_gralcortes').then(response => {
                this.remclientes = response.data;
                this.load = false;   
            }).catch(error => {
                this.load = false;
            });
        },
        // OBTENER CLIENTE
        selectCliente(cliente){
            this.load = true;
            this.clientes = [];
            axios.get('/remcliente/gc_bycliente', {params: { cliente_id: cliente.id }}).then(response => {
                this.remclientes = [];
                this.remclientes.push(response.data);
                this.queryCliente = cliente.name;
                this.load = false;   
            }).catch(error => {
                this.load = false;
            });
        }
    }
}
</script>

<style>
    #listaP{
        position: absolute;
        z-index: 100
    }
</style>