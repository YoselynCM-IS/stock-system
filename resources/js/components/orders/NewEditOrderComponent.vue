<template>
    <div>
        <h4><b>{{ order == 0 ? 'Nuevo':'Editar' }} pedido</b></h4><hr>
        <b-row>
            <b-col>
                <b-form-group label="PROVEEDOR:" label-class="font-weight-bold">
                    <b-form-select v-model="form.editorial" autofocus 
                        :disabled="load" :options="options">
                    </b-form-select>
                </b-form-group> 
            </b-col>
            <b-col>
                <search-select-cliente-component 
                    :titulo="'PARA:'" 
                    :status="'activo'" 
                    :load="load" 
                    :clientename="form.cliente_name" 
                    :tipo="tipo" 
                    @sendCliente="sendCliente">
                </search-select-cliente-component>
            </b-col>
            <b-col sm="2">
                <b-button @click="save_pedido()" class="mt-2" variant="success" pill block
                    :disabled="(load || this.form.libros.length == 0 || this.form.cliente_id == null || form.editorial == null)">
                    <i class="fa fa-check-circle"></i> Guardar
                </b-button>
            </b-col>
        </b-row>
        <table-pedidos-component 
            :load="load" 
            :ftotalq="form.total_quantity" 
            :ftotal="form.total_bill" 
            :flibros="form.libros" 
            :tipo="tipo"
            @sendPedidos="sendPedidos">
        </table-pedidos-component>
    </div>
</template>

<script>
import TablePedidosComponent from '../funciones/pedidos/TablePedidosComponent.vue';
import SearchSelectClienteComponent from '../funciones/SearchSelectClienteComponent.vue'
import getEditoriales from '../../mixins/getEditoriales';
import sweetAlert from '../../mixins/sweetAlert';
import setDatosPedido from '../../mixins/setDatosPedido';
export default {
    props: ['order', 'tipo'],
    mixins: [getEditoriales, sweetAlert, setDatosPedido],
    components: { SearchSelectClienteComponent, TablePedidosComponent },
    data(){
        return {
            load: false,
            form: {
                id: null,
                editorial: null,
                cliente_id: null,
                cliente_name: null,
                total_bill: 0,
                total_quantity: 0,
                libros: []
            }
        }
    },
    created: function(){
        if(this.order != 0 && this.order != null){
            this.form.id = this.order.id;
            this.form.editorial = this.order.provider;
            this.form.cliente_id = this.order.cliente_id;
            this.form.cliente_name = this.order.destination
            this.form.total_bill = this.order.total_bill;
            this.form.total_quantity = 0;
            this.order.elements.forEach(e => {
                this.form.libros.push(this.setDatosPedido(e.id, e.quantity, e.unit_price, e.total, e.tipo, null, e.libro ));
                this.form.total_quantity += e.quantity;
            });
        }
        this.get_editoriales();
    },
    methods: {
        sendCliente(cliente){
            this.form.cliente_id = cliente.id;
            this.form.cliente_name = cliente.name;
        },
        sendPedidos(form){
            this.form.libros = form.libros;
            this.form.total_bill = form.total;
        },
        save_pedido(){
            this.load = true;
            var methodaxios = null;
            
            if(this.order == 0) methodaxios = axios.post('/order/store', this.form);
            else methodaxios = axios.put('/order/update', this.form);

            methodaxios.then(response => {
                this.messageAlert('center', 'success', 'El pedido se guardo correctamente.', '/information/pedidos/proveedor', 'close-opener');
                this.load = false;
            }).catch(error => {
                this.load = false;
            });
        }
    }
}
</script>

<style>

</style>