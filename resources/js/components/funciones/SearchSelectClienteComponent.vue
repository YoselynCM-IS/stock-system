<template>
    <div>
        <b-form-group :label="titulo" label-class="font-weight-bold">
            <b-input v-model="queryCliente" @keyup="mostrarClientes()" autofocus
                style="text-transform:uppercase;" :disabled="load" required
                placeholder="BUSCAR CLIENTE">
            </b-input>
            <div class="list-group" v-if="clientes.length" id="listP">
                <a href="#" v-bind:key="i" class="list-group-item list-group-item-action" 
                    v-for="(cliente, i) in clientes" @click="selectCliente(cliente)">
                    {{ cliente.name }}
                </a>
            </div>
        </b-form-group>
    </div>
</template>

<script>
import searchCliente from '../../mixins/searchCliente';
export default {
    props: ['load', 'titulo'],
    mixins: [searchCliente],
    methods: {
        selectCliente(cliente){
            this.queryCliente = cliente.name;
            this.clientes = [];
            this.$emit('sendCliente', cliente);
        }
    }
}
</script>

<style>

</style>