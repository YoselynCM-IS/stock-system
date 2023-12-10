<template>
    <div>
        <b-row>
            <b-col sm="4">
                <b-form-select v-model="corte_id" :options="options" 
                    required :disabled="load" autofocus id="input-periodo"
                    @change="getPeriodoRem()">
                </b-form-select>
            </b-col>
            <b-col>
                <b-row>
                    <b-col>
                        <b-form-input v-model="queryCliente" :disabled="load" 
                            style="text-transform:uppercase;" placeholder="Buscar cliente"
                            @keyup="mostrarClientes()">
                        </b-form-input>
                        <div class="list-group" v-if="clientes.length" id="listP">
                            <a href="#" v-bind:key="i" class="list-group-item list-group-item-action" 
                                v-for="(cliente, i) in clientes" @click="selectCliente(cliente)">
                                {{ cliente.name }}
                            </a>
                        </div>
                    </b-col>
                    <b-col sm="2">
                        <b-button variant="primary" pill target="blank"
                            :disabled="load || !statePeriodo" size="sm" block
                            :href="`/historial/pagos/registrar/${cliente_id}/${corte_id}`">
                            Pagos
                        </b-button>
                    </b-col>
                </b-row>
            </b-col>
            <b-col sm="2" class="text-right">
                <b-button variant="success" :disabled="load" target="blank" 
                    :href="`/historial/crear_remision`" pill>
                    <i class="fa fa-plus"></i> Crear remisión
                </b-button>
            </b-col>
        </b-row>
        <b-row class="mb-3 mt-3">
            <b-col sm="10">
                <!-- PAGINACIÓN -->
                <pagination size="default" :limit="1" :data="dataRemisiones" 
                    @pagination-change-page="getResults">
                    <span slot="prev-nav"><i class="fa fa-angle-left"></i></span>
                    <span slot="next-nav"><i class="fa fa-angle-right"></i></span>
                </pagination>
            </b-col>
        </b-row>
        <b-table :items="dataRemisiones.data" :fields="fields">
            <template v-slot:cell(total)="row">
                ${{ row.item.total | formatNumber }}
            </template>
            <template v-slot:cell(pagos)="row">
                ${{ row.item.pagos | formatNumber }}
            </template>
            <template v-slot:cell(total_devolucion)="row">
                ${{ row.item.total_devolucion | formatNumber }}
            </template>
            <template v-slot:cell(total_pagar)="row">
                ${{ row.item.total_pagar | formatNumber }}
            </template>
            <template v-slot:cell(detalles)="row">
                <b-button :href="`/remisiones/details/${row.item.id}`" 
                    target="blank" variant="info" pill :disabled="load">
                    <i class="fa fa-info-circle"></i>
                </b-button>
            </template>
            <template v-slot:cell(devolucion)="row">
                <b-button :href="`/historial/registrar_devolucion/${row.item.id}`" 
                    target="blank" variant="dark" pill 
                    :disabled="load || row.item.total_pagar <= 0">
                    <i class="fa fa-edit"></i>
                </b-button>
            </template>
        </b-table>
    </div>
</template>

<script>
import formatNumber from '../../mixins/formatNumber';
import searchCliente from '../../mixins/searchCliente';
import setCortes from '../../mixins/setCortes';
export default {
    props: ['corte_id'],
    mixins: [formatNumber, searchCliente, setCortes],
    data(){
        return {
            load: false,
            dataRemisiones: {},
            fields: [
                { key: 'id', label: 'Folio' },
                { key: 'fecha_creacion', label: 'Fecha de creación' },
                { key: 'cliente.name', label: 'Cliente' },
                { key: 'total', label: 'Salida' },
                { key: 'pagos', label: 'Pagos' },
                { key: 'total_devolucion', label: 'Devolución' },
                { key: 'total_pagar', label: 'Pagar' },
                { key: 'detalles', label: 'Detalles' },
                { key: 'devolucion', label: 'Devolución' }
            ],
            cliente_id: null,
            statePeriodo: false,
            options: [],
        }
    },
    created: function(){
        this.getResults();
        this.getCortes();
    },
    methods: {
        getResults(page = 1){
            if(this.cliente_id == null){
                this.getRemisiones(page);
            } else {
                this.get_remisionesPeriodoCliente(page);
            }
        },
        getCortes(){
            this.load = true;
            axios.get('/cortes/get_all').then(response => {
                this.options = this.setCortes(response.data, null);
                this.options.unshift({value: '0', text: 'BUSCAR POR PERIODO', disabled: true});
                this.load = false;
            }).catch(error => {
                this.load = false;
                this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
            });
        },
        getRemisiones(page = 1){
            this.load = true;
            axios.get(`/historial/remisiones_byperiodo?page=${page}`, 
                        {params: {corte_id: this.corte_id}}).then(response => {
                this.dataRemisiones = response.data;
                this.load = false;   
            }).catch(error => {
                this.load = false;
            });
        },
        selectCliente(cliente){
            this.cliente_id = cliente.id;
            this.queryCliente = cliente.name;
            this.clientes = [];
            this.statePeriodo = false;
            this.get_remisionesPeriodoCliente();
        },
        get_remisionesPeriodoCliente(page = 1){
            this.load = true;
            axios.get(`/historial/remisiones/byperiodo_cliente?page=${page}`, 
                        {params: {corte_id: this.corte_id, cliente_id: this.cliente_id}}).then(response => {
                this.dataRemisiones = response.data;
                if(response.data.data.length > 0) this.statePeriodo = true;
                this.load = false;
            }).catch(error => {
                this.load = false;
            });
        },
        getPeriodoRem(){
            location.href = `/historial/remisiones/lista/${this.corte_id}`;
        }
    }
}
</script>