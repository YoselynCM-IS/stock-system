<template>
    <div>
        <div v-if="!load">
            <b-row>
                <b-col>
                    <!-- PAGINACIÓN -->
                    <b-pagination v-model="currentPage"
                        :total-rows="remisiones.length"
                        :per-page="perPage" aria-controls="my-table">
                    </b-pagination>
                </b-col>
                <b-col>
                    <!-- BUSCAR POR CLIENTE -->
                    <b-row class="my-1">
                        <b-col class="text-right" sm="3">
                            <label for="input-cliente">Buscar cliente</label>
                        </b-col>
                        <b-col sm="9">
                            <b-input style="text-transform:uppercase;"
                                v-model="queryCliente" @keyup="mostrarClientes()">
                            </b-input>
                            <div class="list-group" v-if="resultsClientes.length" id="listaL">
                                <a href="#" v-bind:key="i" 
                                    class="list-group-item list-group-item-action" 
                                    v-for="(result, i) in resultsClientes" 
                                    @click="porCliente(result)">
                                    {{ result.name }}
                                </a>
                            </div>
                        </b-col>
                    </b-row>
                </b-col>
            </b-row>
            <!-- REMISIONES -->
            <b-table v-if="remisiones.length" :busy="load" responsive hover 
                        :items="remisiones" :fields="fields"
                        :tbody-tr-class="rowClass" id="my-table" 
                        :per-page="perPage" :current-page="currentPage">
                <!-- <template v-slot:cell(total)="row">
                    ${{ row.item.total | formatNumber }}
                </template>
                <template v-slot:cell(total_devolucion)="row">
                    ${{ row.item.total_devolucion | formatNumber }}
                </template>
                <template v-slot:cell(pagos)="row">
                    ${{ row.item.pagos | formatNumber }}
                </template> -->
                <template v-slot:cell(remisiones)="row">
                    <div>
                        <p v-if="(row.item.total_pagar < row.item.all_total_pagar)">
                            Algunas remisiones ya han sido pagadas en su totalidad, se encuentran como pendientes debido a que algunos pagos han sido ingresados a la cuenta general del cliente.
                        </p>
                        <b-row>
                            <b-col sm="6" class="text-right"><b>SALDO PENDIENTE (REAL)</b>:</b-col>
                            <b-col>${{ row.item.total_pagar | formatNumber }}</b-col>
                        </b-row>
                        <b-row>
                            <b-col sm="6" class="text-right"><b>TOTAL DE REMISIONES</b>:</b-col>
                            <b-col>${{ row.item.all_total_pagar | formatNumber }}</b-col>
                        </b-row>
                    </div>
                    <b-table v-if="row.item.remisiones.length > 0"
                        responsive hover :items="row.item.remisiones" 
                        :fields="fieldsRemisiones" :tbody-tr-class="rowClass">
                        <template v-slot:cell(total_pagar)="data">
                            ${{ data.item.total_pagar | formatNumber }}
                        </template>
                    </b-table>
                    <b-alert v-else show variant="secondary">
                        <i class="fa fa-warning"></i> No se encontraron remisiones pendientes.
                    </b-alert>
                </template>
            </b-table>
        </div>
        <div v-else class="text-center text-primary my-2 mt-5">
            <b-spinner class="align-middle"></b-spinner>
            <strong>Cargando contenido...</strong>
        </div>
    </div>
</template>

<script>
export default {
    data(){
        return {
            remisionesData: {},
            remisiones: [],
            load: false,
            fields: [
                // { key: 'id', label: 'Folio' },
                // { key: 'fecha_creacion', label: 'Fecha de creación' },
                { key: 'cliente_name', label: 'Cliente' },
                { key: 'remisiones', label: 'Remisiones' },
                // { key: 'total', label: 'Salida' },
                // 'pagos',
                // { key: 'total_devolucion', label: 'Devolución' },
                // { key: 'total_pagar', label: 'Pagar' },
                // { key: 'diferencia', label: 'Días' },
                // { key: 'situacion', label: 'Situación' },
            ],
            fieldsRemisiones: [
                { key: 'id', label: 'Folio' },
                { key: 'fecha_creacion', label: 'Fecha de creación' },
                // { key: 'total', label: 'Salida' },
                // 'pagos',
                // { key: 'total_devolucion', label: 'Devolución' },
                { key: 'total_pagar', label: 'Pagar' },
                { key: 'diferencia', label: 'Días' },
                { key: 'situacion', label: 'Situación' },
            ],
            currentPage: 1,
            perPage: 20,
            resultsClientes: [],
            queryCliente: null,
            all_total_total: 0,
            all_total_pagos: 0,
            all_total_pagar: 0,
            searhCliente: false,
            remcliente: {}
        }
    },
    created: function(){
        this.getResults();
    },
    filters: {
        formatNumber: function (value) {
            return numeral(value).format("0,0[.]00"); 
        }
    },
    methods: {
        getResults(){
            this.load = true;
            axios.get(`/remisiones/obtener_pendientes`).then(response => {
                this.remisiones = response.data;
                this.load = false;   
            }).catch(error => {
                this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                this.load = false;
            });
        },
        rowClass(item, type) {
            if(item.diferencia > 30 && item.situacion !== '') return 'table-danger';
            if(item.diferencia > 30 && item.situacion === '') return 'table-warning';
            return;
        },
        // MOSTRAR LOS CLIENTES
        mostrarClientes(){
            if(this.queryCliente.length > 0){
                axios.get('/mostrarClientes', {params: {queryCliente: this.queryCliente}}).then(response => {
                    this.resultsClientes = response.data;
                }); 
            }
            else{
                this.resultsClientes = [];
            }
        },
        // OBTENER REMISIONES
        porCliente(result){
            this.load = true;
            axios.get('/remisiones/by_cliente_pendientes', {params: {cliente_id: result.id}}).then(response => {
                this.resultsClientes = [];
                this.remisiones = [];
                this.remisiones.push(response.data);
                // this.all_total_total = response.data.all_total_total;
                // this.all_total_pagos = response.data.all_total_pagos;
                // this.all_total_pagar = response.data.all_total_pagar;
                // this.remcliente = response.data.remcliente;
                this.queryCliente = result.name;
                // this.searhCliente = true;
                this.load = false;
            });
        }
    }
}
</script>