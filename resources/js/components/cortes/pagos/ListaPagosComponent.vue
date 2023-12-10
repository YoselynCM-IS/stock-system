<template>
    <div>
        <!-- FUNCIONES (ENCABEZADO) -->
        <b-row>
            <b-col>
                <!-- PAGINACIÓN -->
                <pagination size="default" :limit="1" :data="remclientesData" 
                    @pagination-change-page="getResults">
                    <span slot="prev-nav"><i class="fa fa-angle-left"></i></span>
                    <span slot="next-nav"><i class="fa fa-angle-right"></i></span>
                </pagination>
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
            <b-col sm="4" class="text-right">
                <b-button href="/descargar_gralClientes" variant="dark" pill>
                    <i class="fa fa-download"></i> Descargar
                </b-button>
            </b-col>
        </b-row>
        <!-- TABLA DE CLIENTES -->
        <table-remclientes :remclientesData="remclientesData" :load="load"></table-remclientes>
    </div>
</template>

<script>
import searchCliente from '../../../mixins/searchCliente';
import toast from '../../../mixins/toast';
import TableRemclientes from '../partials/TableRemclientes.vue';
export default {
  components: { TableRemclientes },
    mixins: [searchCliente,toast],
    data(){
        return {
            load: false,
            remclientesData: {},
            cliente: null
        }
    },
    mounted: function(){
        this.getResults();
    },
    methods: {
        // OBTENER REMISIONES POR PAGINA
        getResults(page = 1){
            if(this.cliente == null) this.http_all(page);
            else this.selectCliente(this.cliente);
        },
        http_all(page){
            this.load = true;
            axios.get(`/remcliente/index?page=${page}`).then(response => {
                this.remclientesData = response.data;
                this.load = false;   
            }).catch(error => {
                this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                this.load = false;
            });
        },
        // SELECCIONAR CLIENTE
        selectCliente(cliente){
            this.load = true;
            this.cliente = cliente;
            this.clientes = [];
            axios.get('/remcliente/by_cliente', {params: {cliente_id: this.cliente.id}}).then(response => {
                if(response.data.data.length > 0){
                    this.remclientesData = response.data;
                    this.queryCliente = this.cliente.name;
                } else {
                    this.makeToast('warning', 'El cliente seleccionado no tiene una cuenta general registrada');
                }
                this.load = false;
            }).catch(error => {
                this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                this.load = false;
            });
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