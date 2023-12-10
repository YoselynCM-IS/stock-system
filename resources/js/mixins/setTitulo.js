export default {
    methods: {
        setTitulo(tipo){
            if(tipo == 'reunion') return 'Lugar';
            if(tipo == 'videoconferencia') return 'Liga';
            return;
        }
    },
}