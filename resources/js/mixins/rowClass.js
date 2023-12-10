export default {
    methods: {
        rowClass(item, type) {
            if (!item) return
            if (item.estado == 'Iniciado') return 'table-secondary'
            if (item.estado == 'Cancelado') return 'table-danger'
            if (item.total_pagar == 0 && (item.pagos > 0 || item.total_devolucion > 0)) return 'table-success'
            if (item.total_pagar < 0 && (item.pagos > 0 || item.total_devolucion > 0)) return 'table-warning'
        },
    },
}