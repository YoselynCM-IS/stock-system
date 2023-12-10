<template>
    <div>
        <b-card no-body>
            <b-tabs card>
                <b-tab title="Actividades" active>
                    <actividades-component></actividades-component>
                </b-tab>
                <b-tab title="Clientes">
                    <table-clientes :fields="fieldsClientes" :role_id="role_id"
                        :addCliente="false"></table-clientes>
                </b-tab>
            </b-tabs>
        </b-card>
    </div>
</template>

<script>
import ActividadesComponent from '../actividades/ActividadesComponent.vue';
import TableClientes from '../clientes/partials/TableClientes.vue'
export default {
    props: ['role_id', ],
    components: { TableClientes, ActividadesComponent },
    data() {
        return {
            fieldsClientes: [
                {key: 'index', label: 'N.'},
                {key: 'tipo', label: 'Tipo'},
                {key: 'estado.estado', label: 'Estado'},
                {key: 'name', label: 'Cliente'},
                {key: 'contacto', label: 'Contacto'},
                {key: 'detalles', label: ''},
                {key: 'options', label: ''}
            ],
            actividades: {}
        }
    }

}
</script>

<style>

</style>