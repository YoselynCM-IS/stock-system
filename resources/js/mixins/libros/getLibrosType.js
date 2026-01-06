export default {
    data() {
        return {
            resultsLibros: [],
            queryTitulo: null
        }
    },
    methods: {
        // OBTENER LIBROS POR COINCIDENCIA Y POR TIPO
        mostrar_libros(type){
            if(this.queryTitulo.length > 3){
                axios.get('/libro/by_titulo_type', {params: {titulo: this.queryTitulo, type: type}}).then(response => {
                    this.resultsLibros = response.data;
                }).catch(error => {
                    console.log(error);
                });
            } else{
                this.resultsLibros = [];
            }
        },
    },
}