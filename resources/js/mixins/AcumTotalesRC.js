export default {
    data() {
        return {
            total_salida: 0,
            total_pagos: 0,
            total_devolucion: 0,
            total_pagar: 0,
        }
    },
    mounted: function(){
        this.acumular_totales();
    },
    methods: {
        // OBTENER TOTALES DE TODO 
        acumular_totales(){
            axios.get('/remcliente/get_totales').then(response => {
                this.set_totales(response.data[0]); 
            }).catch(error => { });
        }, 
        set_totales(r){
            this.total_salida       = r.total;
            this.total_devolucion   = r.total_devolucion;
            this.total_pagos        = r.total_pagos;
            this.total_pagar        = r.total_pagar;
        },
    },
}