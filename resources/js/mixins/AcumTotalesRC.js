export default {
    data() {
        return {
            totales: []
        }
    },
    mounted: function(){
        this.acumular_totales();
    },
    methods: {
        // OBTENER TOTALES DE TODO 
        acumular_totales(){
            axios.get('/remcliente/get_totales').then(response => {
                this.totales = response.data;
            }).catch(error => { });
        }
    },
}