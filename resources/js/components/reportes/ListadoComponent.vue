<template>
    <div>
        <b-row class="mb-2">
            <b-col>
                <!-- PAGINACIÓN -->
                <pagination size="default" :limit="1" :data="reportes" 
                    @pagination-change-page="http_reportes">
                    <span slot="prev-nav"><i class="fa fa-angle-left"></i></span>
                    <span slot="next-nav"><i class="fa fa-angle-right"></i></span>
                </pagination>
            </b-col>
            <b-col sm="2">
                <b-button variant="dark" pill block @click="noSelect = !noSelect">
                    <i class="fa fa-check-square-o"></i> Revisar
                </b-button>
            </b-col>
            <b-col sm="2">
                <b-button variant="dark" pill block @click="realizar_busquedas()">
                    <i class="fa fa-search"></i> Busqueda
                </b-button>
            </b-col>
        </b-row>
        <p v-html="set_titulo()"></p>
        <b-tabs content-class="mt-3" fill>
            <b-tab title="GENERAL" @click="get_bytypeestado('general')" active>
                <table-reportes :reportes="reportes" :load="load" :noSelect="noSelect"></table-reportes>
            </b-tab>
            <b-tab title="LIBROS" @click="get_bytypeestado('libro')">
                <table-reportes :reportes="reportes" :load="load" :noSelect="noSelect"></table-reportes>
            </b-tab>
        </b-tabs>

        <!-- MODALS -->
        <b-modal ref="modal-busqueda" title="Busqueda por" hide-footer>
            <h6><b>Fecha</b></h6>
            <b-row>
                <b-col>
                    <b-form-datepicker v-model="queryFecha"></b-form-datepicker>
                </b-col>
                <b-col sm="2">
                    <b-button @click="http_bytypeestadofecha()" :disabled="(queryFecha == null) || load"
                        variant="dark" pill size="sm" block>
                        <i class="fa fa-search"></i>
                    </b-button>
                </b-col>
            </b-row><hr>
            <h6><b>Usuario</b></h6>
            <b-row>
                <b-col>
                    <b-form-select v-model="queryUsuario" :options="usuarios"></b-form-select>
                </b-col>
                <b-col sm="2">
                    <b-button @click="http_bytypeestadousuario()" :disabled="(queryUsuario == null) || load"
                        variant="dark" pill size="sm" block>
                        <i class="fa fa-search"></i>
                    </b-button>
                </b-col>
            </b-row><hr>
        </b-modal>
    </div>
</template>

<script>
import TableReportes from './partials/TableReportes.vue';
import getUsuarios from '../../mixins/getUsuarios';
export default {
    components: { TableReportes },
    mixins: [getUsuarios],
    props: ['role_id'],
    data(){
        return {
            reportes: {},
            load: false,
            type: 'general',
            usuarios: [],
            queryFecha: null,
            queryUsuario: null,
            stateFecha: false,
            stateUsuario: false,
            noSelect: true
        }
    },
    created: function(){
        this.http_reportes();
    },
    methods: {
        http_reportes(page = 1){
            this.load = true;
            axios.get(`/reportes/by_type_estado?page=${page}`, {params: 
                {estado: 'capturado', type: this.type}})
            .then(response => {
                this.reportes = response.data;
                this.load = false;   
            }).catch(error => {
                this.load = false;
            });
        },
        get_bytypeestado(type){
            this.type = type;
            if(this.queryFecha == null && this.queryUsuario == null)
                this.http_reportes();
            if(this.queryFecha != null)
                this.http_bytypeestadofecha();
            if(this.queryUsuario != null)
                this.http_bytypeestadousuario();
        },
        realizar_busquedas(){
            this.getUsuarios(0);
            this.queryFecha = null;
            this.queryUsuario = null;
            this.stateFecha = false,
            this.stateUsuario = false;
            this.$refs['modal-busqueda'].show();
        },
        http_bytypeestadofecha(page = 1){
            this.load = true;
            axios.get(`/reportes/by_type_estado_fecha?page=${page}`, {params: 
                {estado: 'capturado', type: this.type, fecha: this.queryFecha}})
            .then(response => {
                this.reportes = response.data;
                this.stateFecha = true;
                this.$refs['modal-busqueda'].hide();
                this.load = false;   
            }).catch(error => {
                this.load = false;
            });
        },
        set_titulo(){
            if(this.stateFecha) return `<h6>Busqueda por <b>fecha</b>: ${this.queryFecha}</h6>`;
            if(this.stateUsuario) return `<h6>Busqueda por <b>usuario</b></h6>`;
        },
        http_bytypeestadousuario(page = 1){
            this.load = true;
            axios.get(`/reportes/by_type_estado_usuario?page=${page}`, {params: 
                {estado: 'capturado', type: this.type, user_id: this.queryUsuario}})
            .then(response => {
                this.reportes = response.data;
                this.stateUsuario = true;
                this.$refs['modal-busqueda'].hide();
                this.load = false;   
            }).catch(error => {
                this.load = false;
            });
        }
    }
}
</script>

<style>

</style>