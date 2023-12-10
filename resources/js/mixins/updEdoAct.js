import toast from './toast';
export default {
    props: ['actividad'],
    mixins: [toast],
    data(){
        return {
            load: false
        }
    },
    methods: {
        onUpdate(){
            if(this.actividad.observaciones.length > 5){
                this.load = true;
                axios.put('/actividades/update_estado', this.actividad).then(response => {
                    this.$emit('updatedActEstado', true);
                    this.load = false;
                }).catch(error => {
                    this.load = false;
                });
            } else {
                this.makeToast('warning', 'Es necesario agregar alguna observación de la actividad.');
            }
        }
    },
}