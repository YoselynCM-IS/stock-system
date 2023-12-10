<template>
    <div>
        <b-badge :variant="set_variant()" v-html="set_message()"></b-badge>
        <i v-if="observations" class="fa fa-exclamation-circle" :id="`popover-obs${id}`"></i>
        <b-popover
            :target="`popover-obs${id}`"
            placement="right"
            title="Observaciones"
            triggers="hover focus"
            variant="info">
            <p v-html="observations"></p>
        </b-popover>
    </div>
</template>

<script>
export default {
    props: ['id', 'status', 'observations'],
    methods: {
        set_variant(){
            if(this.status === 'espera') return "secondary";
            if(this.status === 'cancelado') return "danger";
            if(this.status === 'rechazado') return "dark";
            if(this.status === 'completo') return "success";
            if(this.status === 'incompleto') return "warning";
        },
        set_message(){
            if(this.status === 'espera') return `en espera`;
            if(this.status === 'cancelado') return this.status;
            if(this.status === 'rechazado') return this.status;
            if(this.status === 'completo') return `recibido<br>(completo)`;
            if(this.status === 'incompleto') return `recibido<br>(incompleto)`;
        }
    }
}
</script>

<style>

</style>