export default {
    data(){
        return {
            resultslibros: [],
            queryISBN: null,
            resultsISBNs: []
        }
    },
    methods: {
        getLibros(titulo){
            if(titulo != null && titulo.length > 0){
                axios.get('/mostrarLibros', {params: {queryTitulo: titulo}}).then(response => {
                    this.resultslibros = response.data;
                }).catch(error => { });
            } else{
                this.resultslibros = [];
            }
        },
        buscarISBN(){
            if(this.queryISBN.length > 0){
                axios.get('/buscarISBN', {params: {isbn: this.queryISBN}}).then(response => {
                    this.resultsISBNs = response.data;
                }).catch(error => { });
            } else{
                this.resultsISBNs = [];
            }
        }
    },
}