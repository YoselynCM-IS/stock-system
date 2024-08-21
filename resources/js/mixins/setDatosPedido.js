export default {
    methods: {
        // ASIGNAR VALORES PARA PEDIDO Y ORDEN
        setDatosPedido(id, quantity, price, total, tipo, pack_id, libro){
            return {
                id: id,
                quantity: quantity,
                price: price,
                total: total,
                tipo: tipo,
                pack_id: pack_id,
                libro: { id: libro.id, ISBN: libro.ISBN, titulo: libro.titulo, type: libro.type}
            };
        }
    },
}