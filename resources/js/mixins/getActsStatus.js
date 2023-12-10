export default {
    data(){
        return {
            load: false,
            actividades: [],
        }
    },
    methods: {
        actividades_bystatus(estado){
            this.load = true;
            this.actividades = [];
            axios.get(`/actividades/by_user_estado`, {params: {estado: estado}}).then(response => {
                this.actividades = response.data;
                this.load = false;
            }).catch(error => {
                this.load = false;
            });
        },
    },
}