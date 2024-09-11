<template>
    <div>
        <b-row>
            <b-col class="text-right">
                <b-button href="/descargar_clientes" variant="dark" pill block><i class="fa fa-download"></i> Lista</b-button>
            </b-col>
            <!-- AGREGAR NUEVO CLIENTE -->
            <b-col class="text-right">
                <b-button v-if="(role_id === 1 || role_id === 2 || role_id == 5|| role_id == 9 || role_id == 6 || role_id == 10)" 
                            variant="success" @click="newCliente()" pill block>
                    <i class="fa fa-plus"></i> Agregar cliente
                </b-button>
            </b-col>
        </b-row>
        <!-- MODALS -->
        <!-- MODAL PARA AGREGAR UN CLIENTE -->
        <b-modal id="modal-nuevoCliente" title="Nuevo cliente" hide-footer size="xl">
            <new-client-component :form="form" :edit="false" @actualizarClientes="actClientes"></new-client-component>
        </b-modal>
    </div>
</template>

<script>
import sweetAlert from '../../../mixins/sweetAlert';
export default {
    props: ['role_id'],
    mixins: [sweetAlert],
    data(){
        return {
            form: {
                tipo: null,
                name: null,
                contacto: null,
                user_id: null,
                condiciones_pago: null,
                direccion: null,
                estado_id: null,
                telefono: null,
                tel_oficina: null,
                email: null,
                fiscal: null,
                rfc: null
            }
        }
    },
    methods: {
        newCliente(){
            this.form = {
                tipo: null,
                name: null,
                contacto: null,
                user_id: null,
                condiciones_pago: null,
                direccion: null,
                estado_id: null,
                telefono: null,
                tel_oficina: null,
                email: null,
                fiscal: null,
                rfc: null
            };
            this.$bvModal.show('modal-nuevoCliente');
        },
        // AGREGAR CLIENTE A LA LISTA (EVENTO)
        actClientes(cliente){
            this.$bvModal.hide('modal-nuevoCliente');
            this.messageAlert('center', 'success', 'El cliente se guardo correctamente.', null, 'reload');
        },
    }
}
</script>

<style>

</style>