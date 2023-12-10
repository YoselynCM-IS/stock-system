export default {
    data() {
        return {
            queryCliente: null,
            clientes: []
        }
    },
    methods: {
        // MOSTRAR CLIENTES
        mostrarClientes(){
            if(this.queryCliente.length > 0){
                axios.get('/mostrarClientes', {params: {queryCliente: this.queryCliente}}).then(response => {
                    this.clientes = response.data;
                }); 
            } else {
                this.clientes = [];
            }
        },
        mostrarClientesByTipo(tipo){
            if(this.queryCliente.length > 0){
                axios.get('/clientes/by_tipo', {params: {queryCliente: this.queryCliente, tipo: tipo}}).then(response => {
                    this.clientes = response.data;
                }); 
            } else {
                this.clientes = [];
            }
        }
    },
}