<template>
    <div>
        <b-badge :variant="assign_estado()">{{ estado }}</b-badge>
        <i v-if="comentarios" class="fa fa-exclamation-circle" 
            :id="`popover-obs${id}`"></i>
        <b-popover :target="`popover-obs${id}`"
            placement="right" title="Comentarios"
            triggers="hover focus">
            <p v-html="comentarios"></p>
        </b-popover>
    </div>
</template>

<script>
export default {
    props: ['id', 'comentarios', 'estado'],
    methods: {
        assign_estado(){
            if (this.estado == 'proceso') return 'secondary';
            if (this.estado == 'cancelado') return 'danger';
            if (this.estado == 'de inventario') return 'success';
            if (this.estado == 'en orden') return 'primary';
        }
    }
}
</script>

<style>

</style>