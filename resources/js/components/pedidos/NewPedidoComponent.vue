<template>
    <div>
        <b-row>
            <b-col sm="6">
                <search-select-cliente-component :titulo="'PARA:'" :load="load" @sendCliente="sendCliente"></search-select-cliente-component>
            </b-col>
            <b-col></b-col>
            <b-col sm="2">
                <b-button @click="save_pedido()" class="mt-2" variant="success" pill block
                    :disabled="(load || this.form.total_quantity <= 0 || this.form.cliente_id == null)">
                    <i class="fa fa-check-circle"></i> Guardar
                </b-button>
            </b-col>
        </b-row>
        <table-pedidos-component :load="load" @sendPedidos="sendPedidos"></table-pedidos-component>
    </div>
</template>

<script>
import sweetAlert from '../../mixins/sweetAlert';
import TablePedidosComponent from '../funciones/pedidos/TablePedidosComponent.vue';
import SearchSelectClienteComponent from '../funciones/SearchSelectClienteComponent.vue';
export default {
    components: { SearchSelectClienteComponent, TablePedidosComponent },
    mixins: [sweetAlert],
    data(){
        return {
            form: {
                cliente_id: null,
                total_quantity: 0,
                total: 0,
                libros: []
            },
            load: false,
        }
    },
    methods: {
        save_pedido(){
            this.load = true;
            axios.post('/pedido/store', this.form).then(response => {
                this.messageAlert('center', 'success', 'El pedido se guardo correctamente.', null, 'reload');
                this.load = false;
            }).catch(error => {
                this.load = false;
            });
        },
        sendCliente(cliente){
            this.form.cliente_id = cliente.id;
        },
        sendPedidos(form){
            this.form.total_quantity = form.total_quantity;
            this.form.total = form.total;
            this.form.libros = form.libros;
        }
    }
}
</script>

<style>

</style>