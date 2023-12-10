export default {
    methods: {
        makeToast(variant = null, descripcion) {
            this.$bvToast.toast(descripcion, {
                title: 'Mensaje',
                variant: variant,
                solid: true
            })
        }
    }
}