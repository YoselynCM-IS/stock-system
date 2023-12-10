export default {
    methods: {
        setCortes(cortes, corte_id){
            let options = [];
            options.push({
                value: null, text: 'Selecciona una opción',
                disabled: true
            });
            cortes.forEach(corte => {
                if(corte.id !== corte_id){
                    options.push({
                        value: corte.id,
                        text: `Temporada ${corte.tipo} ${corte.inicio} / ${corte.final}`
                    });
                }
            });
            return options;
        }
    },
}