export default {
    data(){
        return {
            usuarios: []
        }
    },
    methods: {
        getUsuarios(role_id){
            axios.get('/clientes/get_usuarios', {params: {role_id: role_id}}).then(response => {
                let users = response.data;
                this.usuarios.push({ value: null, text: 'Selecciona una opción', disabled: true});
                users.forEach(u => {
                    this.usuarios.push({ value: u.id, text: u.name });
                });
            }).catch(error => { });
        },
    },
}