<template>
    <div>
        <b-row v-if="role_id != 7" class="mb-4">
            <!-- <b-col>
                <b-form-select v-model="tipo_cliente" :options="options" 
                    @change="get_by_tipocliente()"></b-form-select>
            </b-col> -->
            <!-- <b-col sm="7">
                <b-input v-model="queryCliente" @keyup="mostrarClientesByTipo(tipo_cliente)" autofocus
                    style="text-transform:uppercase;" :disabled="(load || misActs)" required
                    placeholder="BUSCAR CLIENTE">
                </b-input>
                <div class="list-group" v-if="clientes.length" id="listP">
                    <a href="#" v-bind:key="i" class="list-group-item list-group-item-action" 
                        v-for="(cliente, i) in clientes" @click="selectCliente(cliente)">
                        {{ cliente.name }}
                    </a>
                </div>
            </b-col> -->
            <b-col sm="5"></b-col>
            <b-col sm="7">
                <b-row>
                    <b-col>
                        <b-button variant="dark" pill block 
                            :disabled="load" @click="getCompleted('completado')">
                            <i class="fa fa-check-square-o"></i> Completadas
                        </b-button>
                    </b-col>
                    <b-col>
                        <b-button variant="danger" pill block 
                            :disabled="load" @click="getCompleted('cancelado')">
                            <i class="fa fa-close"></i> Canceladas
                        </b-button>
                    </b-col>
                    <!-- <b-col>
                        <b-button :variant="`${!misActs ? 'dark':'primary'}`" pill block 
                            :disabled="(load || misActs || queryEstado == 'completado')" 
                            @click="http_byusertipoestado()">
                            <i class="fa fa-list-alt"></i> Mis actividades
                        </b-button>
                    </b-col> -->
                    <b-col>
                        <b-button variant="success" pill block :disabled="load"
                            @click="addActividad()">
                            <i class="fa fa-plus"></i> Nueva actividad
                        </b-button>
                    </b-col>
                </b-row>
            </b-col>
        </b-row>
        <b-tabs content-class="mt-2" fill>
            <b-tab title="Vencido" @click="actividades_bystatus('vencido')">
                <tipo-actividad-component :load="load" :actividades="actividades" @updatedActEstado="updatedActEstado" :role_id="role_id"></tipo-actividad-component> 
            </b-tab>
            <b-tab title="Hoy" @click="actividades_byfechaactual()" active>
                <tipo-actividad-component :load="load" :actividades="actividades" @updatedActEstado="updatedActEstado" :role_id="role_id"></tipo-actividad-component> 
            </b-tab>
            <b-tab title="Proximo" @click="actividades_bystatus('proximo')">
                <tipo-actividad-component :load="load" :actividades="actividades" @updatedActEstado="updatedActEstado" :role_id="role_id"></tipo-actividad-component> 
            </b-tab>
        </b-tabs>
        <!-- MODASL -->
        <!-- AGREGAR ACTIVIDAD -->
        <b-modal id="modal-newActividad" title="Nueva actividad" hide-footer size="lg">
            <new-actividad-component @actividadSaved="actividadSaved"></new-actividad-component>
        </b-modal>
    </div>
</template>

<script>
import TipoActividadComponent from './partials/TipoActividadComponent.vue';
import searchCliente from '../../mixins/searchCliente';
import getActsStatus from '../../mixins/getActsStatus';
import sweetAlert from '../../mixins/sweetAlert';
export default {
    props: ['tipo_cliente', 'role_id'],
    components: {TipoActividadComponent},
    mixins: [searchCliente, getActsStatus, sweetAlert],
    data(){
        return {
            queryEstado: 'pendiente',
            queryTipo: 'cita',
            cliente_id: null,
            misActs: false,
            options: [
                { value: 'CLIENTE', text: 'CLIENTE' },
                { value: 'DISTRIBUIDOR', text: 'DISTRIBUIDORES' },
                { value: 'PROSPECTO', text: 'PROSPECTOS' }
            ]
        }
    },
    mounted: function(){
        this.actividades_byfechaactual();
    },
    methods: {
        actividades_byfechaactual(){
            this.load = true;
            this.actividades = [];
            axios.get(`/actividades/by_user_fecha_actual`).then(response => {
                this.actividades = response.data;
                this.load = false;
            }).catch(error => {
                this.load = false;
            });
        },
        getResults(){
            this.get_acts_bytipoestado();
        },
        get_acts_bytipoestado(tipo){
            this.queryTipo = tipo;
            if(this.cliente_id == null && !this.misActs){
                this.http_bytipoestado();
            }
            if(this.cliente_id > 0 && !this.misActs) {
                this.http_byclientetipoestado();
            }
            if(this.cliente_id == null && this.misActs) {
                this.http_byusertipoestado();
            }
        },
        http_bytipoestado(){
            this.load = true;
            this.actividades = [];
            axios.get(`/actividades/by_tipo_estado`, {
                params: {clientetipo: this.tipo_cliente, estado: this.queryEstado, tipo: this.queryTipo}
            }).then(response => {
                this.actividades = response.data;
                this.load = false;
            }).catch(error => {
                this.load = true;
            });
        },
        addActividad(){
            this.$bvModal.show('modal-newActividad');
        },
        actividadSaved(actividad){
            this.$bvModal.hide('modal-newActividad');
            this.messageAlert('center', 'success', 'La actividad se guardo correctamente.', null, 'reload');
        },
        selectCliente(cliente){
            this.clientes = [];
            this.queryCliente = cliente.name;
            this.cliente_id = cliente.id;
            this.http_byclientetipoestado();
        },
        http_byclientetipoestado(){
            this.load = true;
            this.actividades = [];
            axios.get(`/actividades/by_cliente_tipo_estado`, {
                params: {cliente_id: this.cliente_id, estado: this.queryEstado, tipo: this.queryTipo}
            }).then(response => {
                this.actividades = response.data;
                this.load = false;
            }).catch(error => {
                this.load = true;
            });
        },
        http_byusertipoestado(){
            this.load = true;
            this.actividades = [];
            axios.get(`/actividades/by_userid_tipo_estado`, {
                params: {clientetipo: this.tipo_cliente, estado: this.queryEstado, tipo: this.queryTipo}
            }).then(response => {
                this.actividades = response.data;
                this.misActs = true;
                this.load = false;
            }).catch(error => {
                this.load = true;
            });
        },
        updatedActEstado(){
            this.http_byusertipoestado();
        },
        getCompleted(status){
            location.href = `/information/actividades/get_status/${status}`;
        },
        get_by_tipocliente(){
            location.href = `/information/actividades/lista`;
        }
    }
}
</script>

<style>

</style>