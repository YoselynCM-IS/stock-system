export default {
    data(){
        return {
            load: false,
            clientesData: {},
            clientes: [],
            busquedaByName: false,
        }
    },
    methods: {
        http_clientes(ruta, page = 1){
            this.load = true;
            axios.get(`/clientes/${ruta}?page=${page}`).then(response => {
                this.setResultados(response.data);
            }).catch(error => {
                this.load = true;
            });
        },
        // MOSTRAR TODOS LOS CLIENTES
        http_byname(ruta, page = 1){
            this.load = true;
            axios.get(`/clientes/${ruta}?page=${page}`, {params: {cliente: this.queryCliente}}).then(response => {
                this.setResultados(response.data);
                this.busquedaByName = true;
            }).catch(error => {
                this.load = true;
                this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
            }); 
        },
        // OBTENER CLIENTES SEGUN STATUS
        http_bynamestatus(status, page = 1){
            if(this.queryCliente.length > 3){
                this.load = true;
                axios.get(`/clientes/by_namestatus?page=${page}`, {params: {cliente: this.queryCliente, status: status}}).then(response => {
                    this.setResultados(response.data);
                    this.busquedaByName = true;
                }).catch(error => {
                    this.load = true;
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                }); 
            }
        },
        setResultados(resultados){
            this.clientesData = resultados;
            this.clientes = resultados.data;
            this.load = false;
        }
    },
}