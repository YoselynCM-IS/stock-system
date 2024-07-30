<template>
    <div>
        <h4><b>{{ pedido == 0 ? 'Nuevo':'Editar' }} pedido</b></h4><hr>
        <b-row>
            <b-col sm="6">
                <search-select-cliente-component :titulo="'PARA:'" :status="'activo'" :load="load" :clientename="form.cliente_name" @sendCliente="sendCliente"></search-select-cliente-component>
            </b-col>
            <b-col></b-col>
            <b-col sm="2">
                <b-button @click="save_pedido()" class="mt-2" variant="success" pill block
                    :disabled="(load || this.form.total_quantity <= 0 || this.form.cliente_id == null || this.form.libros.length == 0)">
                    <i class="fa fa-check-circle"></i> Guardar
                </b-button>
            </b-col>
        </b-row>
        <table-pedidos-component :load="load" :ftotalq="form.total_quantity" :ftotal="form.total" :flibros="form.libros" @sendPedidos="sendPedidos"></table-pedidos-component>
    </div>
</template>

<script>
import sweetAlert from '../../mixins/sweetAlert';
import TablePedidosComponent from '../funciones/pedidos/TablePedidosComponent.vue';
import SearchSelectClienteComponent from '../funciones/SearchSelectClienteComponent.vue';
export default {
    props: ['pedido'],
    components: { SearchSelectClienteComponent, TablePedidosComponent },
    mixins: [sweetAlert],
    data(){
        return {
            form: {
                id: null,
                cliente_id: null,
                cliente_name: null,
                total_quantity: 0,
                total: 0,
                libros: []
            },
            load: false,
        }
    },
    created: function(){
        if(this.pedido != 0 && this.pedido != null){
            this.form.id = this.pedido.id;
            this.form.cliente_id = this.pedido.cliente_id;
            this.form.cliente_name = this.pedido.cliente.name;
            this.form.total_quantity = this.pedido.total_quantity;
            this.form.total = this.pedido.total;
            this.pedido.peticiones.forEach(peticion => {
                let datos = {
                    id: peticion.id,
                    quantity: peticion.quantity,
                    price: peticion.price,
                    total: peticion.total,
                    tipo: peticion.tipo,
                    libro: { id: peticion.libro.id, ISBN: peticion.libro.ISBN, titulo: peticion.libro.titulo, type: peticion.libro.type}
                }
                this.form.libros.push(datos);
            });
        }
    },
    methods: {
        save_pedido(){
            this.load = true;
            if(this.pedido == 0) {
                axios.post('/pedido/store', this.form).then(response => {
                    this.messageAlert('center', 'success', 'El pedido se guardo correctamente.', '/information/pedidos/cliente', 'close-opener');
                    this.load = false;
                }).catch(error => {
                    this.load = false;
                });
            } else {
                axios.put('/pedido/update', this.form).then(response => {
                    this.messageAlert('center', 'success', 'El pedido se guardo correctamente.', '/information/pedidos/cliente', 'close-opener');
                    this.load = false;
                }).catch(error => {
                    this.load = false;
                });
            }
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