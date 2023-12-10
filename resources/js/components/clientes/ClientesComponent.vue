<template>
    <div>
        <check-connection-component></check-connection-component>
        <table-clientes :fields="fields" :role_id="role_id" :addCliente="true"></table-clientes>
    </div>
</template>

<script>
    export default {
        props: ['role_id'],
        data() {
            return {
                fields: [
                    {key: 'index', label: 'N.'},
                    {key: 'tipo', label: 'Tipo'},
                    {key: 'estado.estado', label: 'Estado'},
                    {key: 'name', label: 'Cliente'},
                    {key: 'email', label: 'Correo'},
                    {key: 'telefono', label: 'Teléfono'},
                    {key: 'user.name', label: 'Responsable'},
                    {key: 'detalles', label: ''},
                    {key: 'editar', label: ''}
                ]
            }
        }
    }
</script>