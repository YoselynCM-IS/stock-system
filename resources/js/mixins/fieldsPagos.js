export default {
    data() {
        return {
            fieldsPagos: [
                {key: 'index', label: 'N.'},
                {key: 'remcliente.cliente.name', label: 'Cliente'},
                {key: 'created_at', label: 'Fecha de registro'},
                'pago',
                {key: 'ingresado_por', label: 'Ingresado por'},
                'nota',
                {key: 'fecha', label: 'Fecha del pago'}
            ],
        }
    }
}