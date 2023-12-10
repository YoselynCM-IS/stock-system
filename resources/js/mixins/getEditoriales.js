export default {
    data(){
        return {
            options: [],
            editoriales: []
        }
    },
    methods: {
        get_editoriales(){
            axios.get('/libro/get_editoriales').then(response => {
                this.editoriales = response.data;
                this.options.push({
                    value: null, text: 'Seleccionar una opción', disabled: true
                });
                this.editoriales.forEach(editorial => {
                    this.options.push({
                        value: editorial.editorial,
                        text: editorial.editorial
                    });
                });
            }).catch(error => { });
        }
    },
}