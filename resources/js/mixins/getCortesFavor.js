import setCortes from './setCortes';
export default {
    mixins: [setCortes],
    data(){
        return {
            load: false,
            options: []
        }
    },
    methods: {
        getCortes(corte_id){
            this.load = true;
            this.options = [];
            axios.get('/cortes/get_all').then(response => {
                this.options = this.setCortes(response.data, corte_id);
                this.load = false;
            }).catch(error => {
                this.load = false;
                this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
            });
        }
    }
}