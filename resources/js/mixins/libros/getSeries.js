export default {
    data() {
        return {
            querySerie: null,
            resultsSeries: [],
        }
    },
    methods: {
        // OBTENER SERIES POR COINCIDENCIA
        showSeries() {
            if(this.querySerie.length > 3){
                axios.get('/libro/serie/get_series', { params: { querySerie: this.querySerie } }).then(response => {
                    this.resultsSeries = response.data;
                }).catch(error => { });
            }
        },
    },
}