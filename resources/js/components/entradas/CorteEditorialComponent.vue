<template>
    <div>
        <div v-if="!load">
            <b-row>
                <b-col>
                    <h5><b>Proveedor: {{ edsCortes.editorial }}</b></h5>
                </b-col>
                <b-col sm="2" class="text-right">
                    <b-button variant="secondary" pill @click="goBack()">
                        <i class="fa fa-arrow-left"></i> Regresar
                    </b-button>
                </b-col>
            </b-row>
            <!-- TOTAL GENERAL DEL CLIENTE -->
            <div>
                <h6><strong>Cuenta general</strong></h6>
                <table-totals :dato="edsCortes" :variant="'dark'"></table-totals>
            </div>
            <!-- DATOS DE LOS CORTES -->
            <div v-for="(corte, i) in edsCortes.cortes" v-bind:key="i"
                class="accordion" role="tablist">
                <div class="mb-3">
                    <b-row>
                        <b-col sm="2"><b>Temporada {{ corte.corte.tipo }}</b></b-col>
                        <b-col><b>{{ corte.corte.inicio }} - {{ corte.corte.final }}</b></b-col>
                        <b-col sm="2">
                            <b-button v-if="corte.total_pagar > 0 && (role_id === 1 || role_id === 2 || role_id == 6)" 
                                @click="registrarPago(corte)"
                                pill size="sm" variant="primary">
                                Realizar pago
                            </b-button>
                        </b-col>
                        <b-col sm="1" class="text-right">
                            <b-button v-b-toggle="`accordion-${corte.corte.id}`" pill variant="info" size="sm"
                                @click="getPagos(corte)" role="tab">
                                Detalles
                            </b-button>
                        </b-col>
                    </b-row>
                    <table-totals :dato="corte" :variant="'secondary'" :favor="true"></table-totals>
                    <!-- Element to collapse -->
                    <b-collapse :id="`accordion-${corte.corte.id}`" accordion="my-accordion" class="mt-2" role="tabpanel">
                        <div v-if="!loadDetails">
                            <b-card v-if="dcorte.corte_id == corte.corte.id">
                                <b-tabs content-class="mt-3" fill>
                                    <b-tab title="Entradas">
                                        <table-entradas :entradas="dcorte.entradas"></table-entradas>
                                    </b-tab>
                                    <b-tab title="Pagos" active>
                                        <table-depositos :depositos="dcorte.depositos" :role_id="role_id"></table-depositos>
                                    </b-tab>
                                    <b-tab title="Devoluciones">
                                        <table-devoluciones :devoluciones="dcorte.devoluciones"></table-devoluciones>
                                    </b-tab>
                                </b-tabs>
                            </b-card>
                        </div>
                        <load-component v-else></load-component>
                    </b-collapse>
                </div>
            </div>
        </div>
        <load-component v-else></load-component>
        <b-modal ref="modal-registrarPago" title="Registrar pago" 
            hide-footer>
            <new-edit-pago-entrada :form="form" :edit="false" @savedPago="savedPago"></new-edit-pago-entrada>
        </b-modal>
    </div>
</template>

<script>
import LoadComponent from '../funciones/LoadComponent.vue';
import TableTotals from '../funciones/TableTotals.vue';
import TableDepositos from './partials/TableDepositos.vue';
import TableDevoluciones from './partials/TableDevoluciones.vue';
import TableEntradas from './partials/TableEntradas.vue';
import NewEditPagoEntrada from './NewEditPagoEntrada.vue';
import sweetAlert from '../../mixins/sweetAlert';
export default {
    props: ['editorial', 'role_id'],
    components: { TableTotals, LoadComponent, TableDepositos, TableEntradas, TableDevoluciones, NewEditPagoEntrada },
    mixins: [sweetAlert],
    data(){
        return {
            load: false,
            edsCortes: {
                enteditoriale_id: null,
                editoriale_id: null,
                editorial: null,
                total: null,
                total_pagos: null,
                total_devolucion: null,
                total_pagar: null,
                cortes: []
            },
            loadDetails: false,
            dcorte: {
                corte_id: null,
                editoriale_id: null,
                depositos: [],
                entradas: [],
                devoluciones: []
            },
            form: {
                id: null,
                enteditoriale_id: null, 
                corte_id: null,
                corte_id_favor: null,
                pago: null,
                fecha: null,
                nota: null,
                total_pendiente: null,
                file: null
            }
        }
    },
    created: function(){
        this.verCortes();
    },
    methods: {
        verCortes(){
            this.load = true;
            axios.get('/entradas/get_cortes', {params: {editorial: this.editorial}}).then(response => {
                this.assign_values(response.data.editoriale, response.data.enteditoriale, response.data.ectotales);
                this.load = false;
            }).catch(error => {
                this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                this.load = false;
            });
        },
        assign_values(editoriale, enteditoriale, ectotales){
            this.edsCortes.editoriale_id = editoriale.id;
            this.edsCortes.editorial = editoriale.editorial;
            this.edsCortes.enteditoriale_id = enteditoriale.id;
            this.edsCortes.total = enteditoriale.total;
            this.edsCortes.total_pagos = enteditoriale.total_pagos;
            this.edsCortes.total_devolucion = enteditoriale.total_devolucion;
            this.edsCortes.total_pagar = enteditoriale.total_pendiente;
            this.edsCortes.cortes = ectotales;
        },
        goBack(){
            window.close();
        },
        registrarPago(corte){
            this.form.id = corte.id; 
            this.form.editoriale_id = corte.editoriale_id;
            this.form.enteditoriale_id = this.edsCortes.enteditoriale_id;
            this.form.corte_id = corte.corte_id;
            this.form.corte_id_favor = null;
            this.form.pago = 0;
            this.form.fecha = null;
            this.form.nota = null;
            this.form.total_pendiente = corte.total_pagar;
            this.form.file = null;
            this.$refs['modal-registrarPago'].show();
        },
        getPagos(corte){
            if(this.dcorte.corte_id != corte.corte.id){
                this.loadDetails = true;
                this.inicializar_values();
                axios.get('/entradas/cortes_details', {params: {
                    enteditoriale_id: this.edsCortes.enteditoriale_id, 
                    corte_id: corte.corte.id,
                    editorial: this.edsCortes.editorial
                }}).then(response => {
                    this.dcorte.corte_id = corte.corte.id;
                    this.dcorte.editoriale_id = this.edsCortes.editoriale_id;
                    this.dcorte.depositos = response.data.entdepositos;
                    this.dcorte.entradas = response.data.entradas;
                    this.dcorte.devoluciones = response.data.entdevoluciones;
                    this.loadDetails = false;
                }).catch(error => {
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                    this.loadDetails = false;
                });
            }
        },
        inicializar_values(){
            this.dcorte.corte_id = null;
            this.dcorte.editoriale_id = null;
            this.dcorte.depositos = [];
            this.dcorte.entradas = [];
            this.dcorte.devoluciones = [];
        },
        // PAGO GUARDADO
        savedPago(pago){
            this.$refs['modal-registrarPago'].hide();
            this.messageAlert('center', 'success', 'El pago se guardo correctamente.', null, 'reload');
        },
    }
}
</script>

<style>

</style>