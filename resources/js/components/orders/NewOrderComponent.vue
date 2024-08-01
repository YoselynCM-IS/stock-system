<template>
    <div>
        <b-row>
            <b-col>
                <b-form-group label="PROVEEDOR:" label-class="font-weight-bold">
                    <b-form-select v-model="form.editorial" autofocus 
                        :disabled="load" :options="options">
                    </b-form-select>
                </b-form-group> 
            </b-col>
            <b-col>
                <search-select-cliente-component :titulo="'PARA:'" :status="'activo'" :load="load" :clientename="null" :tipo="null" @sendCliente="sendCliente"></search-select-cliente-component>
            </b-col>
            <b-col sm="2">
                <b-button @click="save_pedido()" class="mt-2" variant="success" pill block
                    :disabled="(load || this.form.libros.length == 0 || this.form.cliente_id == null || form.editorial == null)">
                    <i class="fa fa-check-circle"></i> Guardar
                </b-button>
            </b-col>
        </b-row>
        <table-pedidos-component :load="load" :ftotalq="0" :ftotal="0" :flibros="[]" @sendPedidos="sendPedidos" :tipo="null"></table-pedidos-component>>
    </div>
</template>

<script>
import TablePedidosComponent from '../funciones/pedidos/TablePedidosComponent.vue';
import SearchSelectClienteComponent from '../funciones/SearchSelectClienteComponent.vue'
import getEditoriales from '../../mixins/getEditoriales';
import sweetAlert from '../../mixins/sweetAlert';
export default {
    mixins: [getEditoriales, sweetAlert],
    components: { SearchSelectClienteComponent, TablePedidosComponent },
    data(){
        return {
            load: false,
            form: {
                editorial: null,
                cliente_id: null,
                cliente_name: null,
                total_bill: 0,
                libros: []
            }
        }
    },
    created: function(){
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
            axios.post('/order/store', this.form).then(response => {
                this.messageAlert('center', 'success', 'El pedido se guardo correctamente.', null, 'reload');
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