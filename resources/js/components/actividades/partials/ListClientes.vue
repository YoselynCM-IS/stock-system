<template>
    <div>
        <b-card v-for="(cliente, i) in clientes" v-bind:key="i">
            <template #header>
                <b-row>
                    <b-col sm="3" class="text-right"><h6><b>Cliente {{i+1}}</b></h6></b-col>
                    <b-col></b-col>
                    <b-col v-if="quitar" sm="1">
                        <b-button variant="secondary" pill block size="sm"
                            @click="delCliente(i)">
                            <i class="fa fa-minus"></i>
                        </b-button>
                    </b-col>
                </b-row>
            </template>
            <b-list-group flush>
                <b-list-group-item>
                    <b-row>
                        <b-col sm="3" class="text-right"><b>Nombre:</b></b-col>
                        <b-col>{{ cliente.contacto }}</b-col>
                    </b-row>
                </b-list-group-item>
                <b-list-group-item>
                    <b-row>
                        <b-col sm="3" class="text-right"><b>Plantel:</b></b-col>
                        <b-col>{{ cliente.name }}</b-col>
                    </b-row>
                </b-list-group-item>
                <b-list-group-item>
                <b-row>
                    <b-col sm="3" class="text-right"><b>Correo electrónico</b></b-col>
                    <b-col>{{ cliente.email }}</b-col>
                </b-row>
                </b-list-group-item>
                <b-list-group-item>
                <b-row>
                    <b-col sm="3" class="text-right"><b>Teléfono celular</b></b-col>
                    <b-col>{{ cliente.telefono }}</b-col>
                </b-row>
                </b-list-group-item>
                <b-list-group-item>
                <b-row>
                    <b-col sm="3" class="text-right"><b>Teléfono oficina</b></b-col>
                    <b-col>{{ cliente.tel_oficina }}</b-col>
                </b-row>
                </b-list-group-item>
            </b-list-group>
        </b-card>
    </div>
</template>

<script>
export default {
    props: ['clientes', 'quitar'],
    methods: {
        delCliente(i){
            this.clientes.splice(i, 1);
        }
    }
}
</script>

<style>

</style>