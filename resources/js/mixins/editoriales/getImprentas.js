export default {
    data() {
        return {
            load: false,
            imprentas: [],
        }
    },
    methods: {
        // OBTENER IMPRENTAS, DE ACUERDO A TIPO DE LIBRO
        getImprentas(tipo){
            this.load = true;
            this.imprentas = [];
            axios.get(`/entradas/get_imprentas/${tipo}`).then(response => {
                let is = response.data;
                this.imprentas.push({
                    value: null,
                    text: 'Selecciona una opción'
                });
                is.forEach(i => {
                    this.imprentas.push({
                        value: i.id,
                        text: i.imprenta
                    });
                });
                this.load = false;
            }).catch(error => {
                this.load = false;
            });
        }
    }
}