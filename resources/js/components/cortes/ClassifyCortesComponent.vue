<template>
    <div>
        <!-- FUNCIONES (ENCABEZADO) -->
        <b-row>
            <b-col>
                <!-- PAGINACIÓN -->
                <pagination size="default" :limit="1" :data="remisiones" 
                    @pagination-change-page="getResults">
                    <span slot="prev-nav"><i class="fa fa-angle-left"></i></span>
                    <span slot="next-nav"><i class="fa fa-angle-right"></i></span>
                </pagination>
            </b-col>
            <b-col sm="4">
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
            <b-col sm="2" class="text-center">
                <b-button :disabled="selected.length == 0 || cliente_id == null" 
                    @click="selectCorte()" variant="success" pill>
                    <i class="fa fa-check-circle"></i> Agregar a corte
                </b-button>
            </b-col>
            <b-col sm="2" class="text-center">
                <b-button variant="secondary" pill :href="`/manager/cortes`">
                    <i class="fa fa-arrow-left"></i> Regresar
                </b-button>
            </b-col>
        </b-row>
        <!-- TABLA DE REMISIONES -->
        <b-table v-if="remisiones.data" 
            :items="remisiones.data" :fields="fieldsRems"
            :select-mode="selectMode" responsive ref="selectableTable"
            selectable @row-selected="onRowSelected">
            <template v-slot:cell(total)="row">
                ${{ row.item.total | formatNumber }}
            </template>
            <template v-slot:cell(total_devolucion)="row">
                ${{ row.item.total_devolucion | formatNumber }}
            </template>
            <template v-slot:cell(total_pagos)="row">
                ${{ row.item.pagos | formatNumber }}
            </template>
            <template v-slot:cell(total_pagar)="row">
                ${{ row.item.total_pagar | formatNumber }}
            </template>
            <template #thead-top="row">
                <tr>
                    <th colspan="3"></th>
                    <th>${{ suma.total_salida | formatNumber }}</th>
                    <th>${{ suma.total_devolucion | formatNumber }}</th>
                    <th colspan="2" class="text-center">
                        <b-button :disabled="cliente_id == null" 
                            size="sm" @click="selectAllRows" pill variant="dark">
                            <i class="fa fa-check"></i>
                        </b-button>
                    </th>
                </tr>
            </template>
        </b-table>
        <!-- MODALS -->
        <!-- Seleccionar corte -->
        <b-modal ref="show-cortes" hide-footer size="sm" title="Seleccionar corte">
            <select-corte-component :form="form" :options="options" :move="false"
                @remsGuardadas="remsGuardadas"></select-corte-component>
        </b-modal>
    </div>
</template>

<script>
import toast from '../../mixins/toast';
import formatNumber from '../../mixins/formatNumber';
import searchCliente from '../../mixins/searchCliente';
import setCortes from '../../mixins/setCortes';
export default {
    props: ['cortes'],
    mixins: [formatNumber,toast,searchCliente,setCortes],
    data(){
        return {
            load: false,
            remisiones: {},
            fieldsRems: [
                { key: 'id', label: 'Folio' },
                { key: 'fecha_creacion', label: 'Fecha de creación' },
                { key: 'cliente.name', label: 'Cliente' },
                { key: 'total', label: 'Salida' },
                { key: 'total_devolucion', label: 'Devolución' },
                { key: 'total_pagos', label: 'Pagos' },
                { key: 'total_pagar', label: 'Pagar' }
            ],
            selectMode: 'multi',
            selected: [],
            options: [],
            form: {
                corte_id: null,
                cliente_id: null,
                remisiones: []
            },
            cliente_id: null,
            suma: {
                total_salida: 0,
                total_devolucion: 0
            }
        }
    },
    created: function() {
        this.getResults();
    },
    methods: {
        // OBTENER REMISIONES
        getResults(page = 1){
            if(this.cliente_id == null) this.http_remisiones(page);
            else this.http_bycliente(page);
        },
        // OBTENER TODAS LAS REMISIONES
        http_remisiones(page){
            this.load = true;
            axios.get(`/cortes/get_remisiones?page=${page}`).then(response => {
                this.remisiones = response.data;
                this.load = false;
            }).catch(error => {
                this.load = false;
            });
        },
        // SELECCIONAR REMISIONES
        onRowSelected(items) {
            this.selected = items;
            this.suma.total_salida = 0;
            this.suma.total_devolucion = 0;
            this.selected.forEach(select => {
                this.suma.total_salida += select.total;
                this.suma.total_devolucion += select.total_devolucion;
            });
        },
        // SELECCIONAR TODO
        selectAllRows() {
            this.$refs.selectableTable.selectAllRows()
        },
        // SELECCIONAR CLIENTE
        selectCliente(cliente){
            this.cliente_id = cliente.id;
            this.queryCliente = cliente.name;
            this.clientes = [];
            this.http_bycliente();
        },
        // OBTENER REMISIONES POR CLIENTE
        http_bycliente(page = 1){
            this.load = true;
            axios.get(`/cortes/rems_bycliente?page=${page}`, {params: {cliente_id: this.cliente_id}}).then(response => {
                this.remisiones = response.data;
                this.load = false;
            }).catch(error => {
                this.load = false;
            });
        },
        // SELECCIONAR EL CORTE PARA GUARDAR REMISIONES
        selectCorte(){
            this.form.remisiones = this.selected;
            this.form.cliente_id = this.cliente_id;
            this.form.corte_id = null;
            this.options = this.setCortes(this.cortes, null);
            this.$refs['show-cortes'].show();
        },
        // REMISIONES AGREGADAS AL CORTE
        remsGuardadas(){
            this.makeToast('success', 'Las remisiones se agregaron correctamente al corte');
            this.http_bycliente();
            this.$refs['show-cortes'].hide();
        },
    }
}
</script>

<style>
    #listaP{
        position: absolute;
        z-index: 100
    }
</style>