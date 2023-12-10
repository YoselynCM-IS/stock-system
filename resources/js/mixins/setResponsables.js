export default {
    methods: {
        assign_responsables(options, data){
            options.push({
                value: null,
                text: 'Selecciona una opción',
                disabled: true
            });
            data.forEach(responsable => {
                options.push({
                    value: responsable.responsable,
                    text: responsable.responsable
                });
            });

            return options;
        },
    },
}