<template>
    <div>
        <b-row class="mb-2">
            <b-col sm="8">
                <!-- BUSCAR CLIENTE POR NOMBRE -->
                <b-input style="text-transform:uppercase;" v-model="queryCliente" 
                            @keyup="addCliente ? http_byname('by_name'):http_byname('by_name_userid')" 
                            placeholder="BUSCAR CLIENTE"></b-input>
            </b-col>
            <b-col class="text-right">
                <add-descarga v-if="addCliente" :role_id="role_id"></add-descarga>
                <div v-else>
                    <b-button variant="dark" pill @click="newProspecto()" >
                        <i class="fa fa-plus-circle"></i> Agregar prospecto
                    </b-button>
                </div>
            </b-col>
        </b-row>
        <!-- LISTADO DE CLIENTES -->
        <div v-if="!load">
            <div v-if="clientes.length > 0">
                <b-table responsive hover :items="clientes" :fields="fields">
                    <template v-slot:cell(index)="row">
                        {{ row.index + 1 }}
                    </template>
                    <template v-slot:cell(editar)="row">
                        <b-button v-if="role_id === 1 || role_id === 2 || role_id == 6 || role_id == 9" 
                            v-b-modal.modal-editarCliente variant="warning" 
                            style="color: white;" pill size="sm" block
                            @click="editarCliente(row.item, row.index)">
                            <i class="fa fa-pencil"></i>
                        </b-button>
                    </template>
                    <template v-slot:cell(detalles)="row">
                        <b-button variant="info" v-b-modal.modal-detalles pill
                            @click="showDetails(row.item)" size="sm" block>
                            <i class="fa fa-info"></i>
                        </b-button>
                    </template>
                    <template v-slot:cell(options)="row">
                        <b-dropdown variant="dark">
                            <b-dropdown-item @click="showLibros(row.item)">Libros</b-dropdown-item>
                            <b-dropdown-item @click="addRegistro(row.item)">Registro</b-dropdown-item>
                            <b-dropdown-item @click="showSeguimiento(row.item)">Seguimiento</b-dropdown-item>
                        </b-dropdown>
                    </template>
                </b-table>
                <!-- PAGINACIÓN -->
                <pagination size="default" :limit="1" :data="clientesData" 
                    @pagination-change-page="getResults" align="center">
                    <span slot="prev-nav"><i class="fa fa-angle-left"></i></span>
                    <span slot="next-nav"><i class="fa fa-angle-right"></i></span>
                </pagination>
            </div>
            <no-registros-component v-else></no-registros-component>
        </div>
        <load-component v-else></load-component>
        <!-- MODALS -->
        <!-- MODAL PARA MOSTRAR LOS DETALLES DEL CLIENTE -->
        <b-modal id="modal-detalles" title="Información del cliente" hide-footer size="xl">
            <div v-if="!loadDetails" class="mb-5">
                <b-row>
                    <b-col>
                        <b-row class="my-1">
                            <b-col align="right"><b>Tipo de cliente:</b></b-col>
                            <div class="col-md-7">{{datosCliente.tipo}}</div>
                        </b-row>
                        <b-row class="my-1">
                            <b-col align="right">
                                <b>{{(datosCliente.tipo == null || datosCliente.tipo == 'CLIENTE') ? 'Cliente':'Distribuidor'}}:</b>
                            </b-col>
                            <div class="col-md-7">{{datosCliente.name}}</div>
                        </b-row>
                        <b-row class="my-1">
                            <b-col align="right">
                                <b>{{(datosCliente.tipo == null || datosCliente.tipo == 'CLIENTE') ? 'Coordinador':'Comunicarse con'}}:</b>
                            </b-col>
                            <div class="col-md-7">{{datosCliente.contacto}}</div>
                        </b-row>
                    </b-col>
                    <b-col>
                        <b-row class="my-1">
                            <b-col align="right"><b>Responsable del cliente:</b></b-col>
                            <div class="col-md-7">{{datosCliente.user ? datosCliente.user.name:''}}</div>
                        </b-row>
                        <b-row class="my-1">
                            <b-col align="right"><b>Condiciones de pago:</b></b-col>
                            <div class="col-md-7">{{datosCliente.condiciones_pago}}</div>
                        </b-row>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col>
                        <b-row class="my-1">
                            <b-col align="right"><b>Dirección:</b></b-col>
                            <div class="col-md-7">{{datosCliente.direccion}}</div>
                        </b-row>
                        <b-row class="my-1">
                            <b-col align="right"><b>Estado:</b></b-col>
                            <div class="col-md-7">{{datosCliente.estado ? datosCliente.estado.estado:''}}</div>
                        </b-row>
                        <b-row class="my-1">
                            <b-col align="right"><b>Teléfono:</b></b-col>
                            <div class="col-md-7">{{datosCliente.telefono}}</div>
                        </b-row>
                        <b-row class="my-1">
                            <b-col align="right"><b>Teléfono (oficina):</b></b-col>
                            <div class="col-md-7">{{datosCliente.tel_oficina}}</div>
                        </b-row>
                        <b-row class="my-1">
                            <b-col align="right"><b>Correo electrónico:</b></b-col>
                            <div class="col-md-7">{{datosCliente.email}}</div>
                        </b-row>
                    </b-col>
                    <b-col>
                        <b-row class="my-1">
                            <b-col align="right"><b>Dirección fiscal:</b></b-col>
                            <div class="col-md-7">{{datosCliente.fiscal}}</div>
                        </b-row>
                        <b-row class="my-1">
                            <b-col align="right"><b>RFC:</b></b-col>
                            <div class="col-md-7">{{datosCliente.rfc}}</div>
                        </b-row>
                    </b-col>
                </b-row>
            </div>
            <load-component v-else></load-component>
        </b-modal>
        <!-- MODAL PARA AGREGAR CLIENTE -->
        <b-modal id="modal-editarCliente" title="Editar cliente" hide-footer size="xl">
            <new-client-component :form="form" :edit="true" @actualizarClientes="actClientes"></new-client-component>
        </b-modal>
        <!-- MODAL PARA RELACIONAR LIBROS VENDIDOS A ESE CLIENTE -->
        <b-modal id="modal-showLibros" :title="`${cliente_name} - Libros`" hide-footer size="xl">
            <libros-cliente-component :cliente_id="cliente_id" :role_id="role_id"></libros-cliente-component>
        </b-modal>
        <!-- MODAL PARA REGISTRAR LLAMADA DEL CLIENTE -->
        <b-modal id="modal-addRegistro" :title="`${cliente_name} - Agregar registro`" hide-footer size="lg">
            <register-component :cliente_id="cliente_id" @addedSeguimiento="addedSeguimiento"></register-component>
        </b-modal>
        <!-- MODAL PARA REGISTRAR PROSPECTO -->
        <b-modal id="modal-nuevoProspecto" title="Agregar prospecto" hide-footer size="lg">
            <new-prospecto @agregadoProspecto="agregadoProspecto"></new-prospecto>
        </b-modal>
        <!-- MODAL PARA MOSTRAR SEGUIMIENTOS -->
        <b-modal id="modal-showseguimiento" :title="`${cliente_name} - Seguimiento`" hide-footer size="xl">
            <seguimientos-component :cliente_id="cliente_id"></seguimientos-component>
        </b-modal>
    </div>
</template>

<script>
import LoadComponent from '../../cortes/partials/LoadComponent.vue';
import NoRegistrosComponent from '../../funciones/NoRegistrosComponent.vue'
import LibrosClienteComponent from '../LibrosClienteComponent.vue';
import NewClientComponent from '../NewClientComponent.vue';
import getClientes from '../../../mixins/getClientes';
import AddDescarga from './AddDescarga.vue';
import NewProspecto from './NewProspecto.vue';
import RegisterComponent from './RegisterComponent.vue';
import SeguimientosComponent from './SeguimientosComponent.vue';
import sweetAlert from '../../../mixins/sweetAlert';
export default {
    props: ['fields', 'role_id', 'addCliente'],
    mixins: [getClientes, sweetAlert],
    components: {NoRegistrosComponent, LoadComponent, NewClientComponent, LibrosClienteComponent, AddDescarga, NewProspecto, RegisterComponent, SeguimientosComponent},
    data() {
        return {
            posicion: null,
            form: {},
            loadDetails: false,
            datosCliente: {},
            cliente_id: null,
            cliente_name: null,
            queryCliente: null,
        }
    },
    mounted: function(){
        this.getResults();
    },
    methods: {
        // OBTENER TODOS LOS CLIENTES
        getResults(page = 1){
            // if(this.addCliente){
            //     var r1 = 'index';
            //     var r2 = 'by_name';
            // } else {
            //     var r1 = 'by_userid';
            //     var r2 = 'by_name_userid';
            // }
            if(!this.busquedaByName)
                this.http_clientes('index', page);
            else 
                this.http_byname('by_name', page);
        },
        // INICIALIZAR PARA EDITAR CLIENTE
        editarCliente(cliente, i){
            this.posicion = i;
            this.assign_datos(cliente);
        },
        assign_datos(cliente){
            this.form.id = cliente.id;
            this.form.name = cliente.name;
            this.form.contacto = cliente.contacto;
            this.form.email = cliente.email;
            this.form.telefono = cliente.telefono;
            this.form.direccion = cliente.direccion;
            this.form.condiciones_pago = cliente.condiciones_pago;
            this.form.rfc = cliente.rfc;
            this.form.fiscal = cliente.fiscal;
            this.form.tipo = cliente.tipo;
            this.form.user_id = cliente.user_id;
            this.form.estado_id = cliente.estado_id;
            this.form.tel_oficina = cliente.tel_oficina;
        },
        showDetails(cliente){
            this.loadDetails = true;
            axios.get('/clientes/show', {params: {cliente_id: cliente.id}}).then(response => {
                this.datosCliente = response.data;
                this.loadDetails = false;
            }).catch(error => {
                this.loadDetails = false;
                this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
            });
        },
        showLibros(cliente){
            this.cliente_name = cliente.name;
            this.cliente_id = cliente.id;
            this.$bvModal.show('modal-showLibros');
        },
        actClientes(cliente){
            this.$bvModal.hide('modal-editarCliente');
            this.messageAlert('center', 'success', 'El cliente se actualizo correctamente.', null, 'reload');
        },
        addRegistro(cliente){
            this.cliente_name = cliente.name;
            this.cliente_id = cliente.id;
            this.$bvModal.show('modal-addRegistro');
        },
        addedSeguimiento(){
            this.$bvModal.hide('modal-addRegistro');
            this.messageAlert('center', 'success', 'Guardado correctamente.', null, 'info');
        },
        newProspecto(){
            this.$bvModal.show('modal-nuevoProspecto');
        },
        agregadoProspecto(){
            this.$bvModal.hide('modal-nuevoProspecto');
            this.messageAlert('center', 'success', 'El cliente prospecto se agrego correctamente.', null, 'reload');
        },
        showSeguimiento(cliente){
            this.cliente_name = cliente.name;
            this.cliente_id = cliente.id;
            this.$bvModal.show('modal-showseguimiento');
        }
    }

}
</script>

<style>

</style>
NoRegistrosComponent