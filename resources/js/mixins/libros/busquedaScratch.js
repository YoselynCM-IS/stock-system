export default {
    data() {
        return {
            resultsScratch: [],
        }
    },
    methods: {
        // OBTENER PACK POR COINCIDENCIA
        librosScratch(titulo_fisico) {
            axios.get('/libro/all_scratch', { params: { titulo: titulo_fisico } }).then(response => {
                this.resultsScratch = response.data;
            }).catch(error => { });
        },
    },
}