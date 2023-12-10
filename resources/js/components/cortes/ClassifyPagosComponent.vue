<template>
    <div>
        <!-- FUNCIONES (ENCABEZADO) -->
        <b-row>
            <b-col>
                <!-- PAGINACIÓN -->
                <pagination size="default" :limit="1" :data="pagos" 
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
        <!-- TABLA DE PAGOS -->
        <b-table v-if="pagos.data" responsive hover 
            :items="pagos.data" :fields="fieldsPagos"
            :select-mode="selectMode" ref="selectableTable"
            selectable @row-selected="onRowSelected">
            <template v-slot:cell(index)="row">
                {{ row.index + 1 }}
            </template>
            <template v-slot:cell(pago)="row">
                ${{ row.item.pago | formatNumber }}
            </template>
            <template #thead-top="row">
                <tr>
                    <th colspan="3"></th>
                    <th>${{ form.total_selected | formatNumber }}</th>
                    <th colspan="3" class="text-center">
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
            <select-corte-pagos-component :form="form" :options="options" :move="false" 
                :cortes="cortes" @pagosGuardados="pagosGuardados"></select-corte-pagos-component>
        </b-modal>
    </div>
</template>

<script>
import toast from '../../mixins/toast';
import searchCliente from '../../mixins/searchCliente';
import formatNumber from '../../mixins/formatNumber';
import setCortes from '../../mixins/setCortes';
import fieldsPagos from '../../mixins/fieldsPagos';
export default {
    props: ['cortes'],
    mixins: [searchCliente,formatNumber,setCortes,toast,fieldsPagos],
    data(){
        return {
            pagos: {},
            load: false,
            cliente_id: null,
            selected: [],
            selectMode: 'multi',
            form: {
                corte_id: null,
                cliente_id: null,
                pagos: [],
                total_selected: 0,
                corte_id_favor: null
            },
            options: []
        }
    },
    created: function(){
        this.getResults();
    },
    methods: {
        // OBTENER RESULTADOS
        getResults(page = 1){
            if(this.cliente_id == null) this.http_pagos(page);
            else this.http_bypagos(page);
        },
        // OBTENER TODOS LOS PAGOS
        http_pagos(page){
            this.load = true;
            axios.get(`/cortes/get_pagos?page=${page}`).then(response => {
                this.pagos = response.data;
                this.load = false;
            }).catch(error => {
                this.load = false;
            });
        },
        // ELEGIR CLIENTE
        selectCliente(cliente){
            this.cliente_id = cliente.id;
            this.queryCliente = cliente.name;
            this.clientes = [];
            this.http_bypagos();
        },
        // OBTENER PAGOS POR CLIENTE
        http_bypagos(page = 1){
            this.load = true;
            axios.get(`/cortes/pagos_bycliente?page=${page}`, {params: {cliente_id: this.cliente_id}}).then(response => {
                if(!response.data) this.makeToast('warning', 'El cliente seleccionado no cuenta con pagos.');
                else this.pagos = response.data;
                this.load = false;
            }).catch(error => {
                this.load = false;
            });
        },
        // AGREGAR APAGOS A CORTE
        selectCorte(){
            this.form.pagos = this.selected;
            this.form.cliente_id = this.cliente_id;
            this.form.corte_id = null;
            this.form.corte_id_favor = null;
            this.options = this.setCortes(this.cortes, null);
            this.$refs['show-cortes'].show();
        },
        // SELECCIONAR PAGOS
        onRowSelected(items) {
            this.selected = items;
            this.form.total_selected = 0;
            this.selected.forEach(sd => {
                this.form.total_selected += sd.pago;
            });
        },
        // SELECCIONAR TODO
        selectAllRows() {
            this.$refs.selectableTable.selectAllRows()
        },
        // PAGOS GUARDADOS
        pagosGuardados(respuesta){
            if(respuesta) {
                this.makeToast('success', 'Los pagos se agregaron correctamente al corte');
                this.http_bypagos();
            } else {
                this.makeToast('warning', 'Vuelve a elegir los pagos.');
            }
            this.$refs['show-cortes'].hide();
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