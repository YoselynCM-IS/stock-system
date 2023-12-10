export default {
    data() {
        return {
            load: false,
            errors: {}
        }
    },
    methods: {
        catch_error(error){
            this.load = false;
            if (error.response.status === 422) {
                this.errors = error.response.data.errors || {};
            } else {
                this.$bvToast.toast('Ocurrió un problema. Verifica tu conexión a internet y/o actualiza la página para volver a intentar.', {
                    title: 'Mensaje',
                    variant: 'danger',
                    solid: true
                })
            }
        }
    }
}