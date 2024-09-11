<template>
    <div>
        <div v-if="!load">
            <b-row class="mb-2">
                <b-col><h5><b>{{ datosCortes.name }}</b></h5></b-col>
                <b-col sm="2">
                    <b-button variant="secondary" pill block size="sm" @click="goBack()">
                        <i class="fa fa-arrow-left"></i> Regresar
                    </b-button>
                </b-col>
            </b-row>
            <!-- FUNCIONES (ENCABEZADO) -->
            <b-row>
                <b-col><h6 class="mt-3"><strong>Cuenta general</strong></h6></b-col>
                <b-col sm="2">
                    <b-button class="btn btn-dark" pill block
                        :href="`/pagos/download_edocuenta/${datosCortes.cliente_id}`">
                        Edo. de cuenta
                    </b-button>
                </b-col>
                <b-col sm="2">
                    <b-button variant="dark" pill block @click="showFicticios()">
                        <i class="fa fa-money"></i> Ficticios
                    </b-button>
                </b-col>
                <b-col  v-if="datosCortes.adeudos.length > 0" sm="2">
                    <b-button variant="dark" pill block @click="viewAdeudos = !viewAdeudos">
                        <b-icon-eye-slash-fill v-if="viewAdeudos"></b-icon-eye-slash-fill>
                        <b-icon-eye-fill v-else></b-icon-eye-fill> 
                        Adeudos
                    </b-button>
                </b-col>
            </b-row>
            <b-row>
                <b-col :sm="(viewAdeudos && datosCortes.adeudos.length > 0) ? '6':'12'">
                    <!-- TOTAL GENERAL DEL CLIENTE -->
                    <table-totals :dato="datosCortes" :variant="'dark'" :favor="false"></table-totals>
                    <!-- DATOS DE LOS CORTES -->
                    <div v-for="(corte, i) in datosCortes.cortes" v-bind:key="i">
                        <div class="mb-3">
                            <b-row>
                                <b-col class="text-left">
                                    <a type="button" :class="corte.visible ? null : 'collapsed'"
                                        :aria-expanded="corte.visible ? 'true' : 'false'"
                                        aria-controls="collapse-1" @click="corte.visible = !corte.visible">
                                        <!-- {{ corte.visible ? 'Ocultar' : 'Mostrar' }} -->
                                        <b-icon-caret-down-fill v-if="!corte.visible"></b-icon-caret-down-fill>
                                        <b-icon-caret-up-fill v-else></b-icon-caret-up-fill>
                                        <b>Temporada {{ corte.corte }} {{ corte.inicio }} - {{ corte.final }}</b>
                                    </a>
                                </b-col>
                                <b-col sm="2">
                                    <b-button v-if="corte.total_pagar > 0" @click="registrarPago(corte)"
                                        pill block size="sm" variant="dark">
                                        Pago
                                    </b-button>
                                </b-col>
                                <b-col sm="2">
                                    <b-button v-if="corte.total_pagar > 0" @click="addAdeudo(corte)"
                                        pill block size="sm" variant="dark">
                                        Adeudo
                                    </b-button>
                                </b-col>
                            </b-row>
                            <table-totals :dato="corte" :variant="'info'" :favor="true"></table-totals>
                            <b-collapse id="collapse-1" v-model="corte.visible" class="mt-2">
                                <b-tabs content-class="mt-3" fill>
                                    <b-tab title="Pagos" active>
                                        <table-pagos :cortePagar="corte.total_pagar"
                                            :remdepositos="corte.remdepositos" :role_id="role_id"
                                            :cliente_id="corte.cliente_id" :showTitle="false"></table-pagos>
                                    </b-tab>
                                    <b-tab title="Remisiones">
                                        <table-remisiones :remisiones="corte.remisiones" :showTitle="false" :role_id="role_id"></table-remisiones>
                                    </b-tab>
                                </b-tabs>
                            </b-collapse>
                        </div>
                    </div>
                </b-col>
                <b-col v-if="viewAdeudos && datosCortes.adeudos.length > 0" sm="6">
                    <adeudos-component :adeudos="datosCortes.adeudos"></adeudos-component>
                </b-col>
            </b-row>
        </div>
        <load-component v-else></load-component>
        <!-- MODALS -->
        <b-modal ref="modal-regPago" title="Registrar pago" hide-footer>
            <reg-pago-component :form="form" :corte="corte" 
                    @savePayment="savePayment" :tipo="1"></reg-pago-component>
        </b-modal>
        <b-modal ref="modal-ficticios" title="Pagos pendientes (ficticios)" hide-footer size="xl">
            <b-table :items="ficticios" :fields="fieldsFic" responsive>
                <template v-slot:cell(created_at)="row">
                    {{ row.item.created_at | momentDet }}
                </template>
                <template v-slot:cell(pago)="row">
                    ${{ row.item.pago | formatNumber }}
                </template>
                <template v-slot:cell(corte)="row">
                    Temporada {{ row.item.corte.tipo }}: {{ row.item.corte.inicio }} - {{ row.item.corte.final }}
                </template>
                <template #thead-top="row">
                    <tr>
                        <th colspan="5"></th>
                        <th>${{ total_ficticios | formatNumber }}</th>
                    </tr>
                </template>
            </b-table>
        </b-modal>
    </div>
</template>

<script>
import formatNumber from '../../../mixins/formatNumber';
import TableRemisiones from '../partials/TableRemisiones.vue';
import TableTotals from '../../funciones/TableTotals.vue';
import TablePagos from '../partials/TablePagos.vue';
import RegPagoComponent from '../partials/RegPagoComponent.vue';
import toast from '../../../mixins/toast';
import LoadComponent from '../partials/LoadComponent.vue';
import sweetAlert from '../../../mixins/sweetAlert';
import moment from '../../../mixins/moment';
import AdeudosComponent from '../partials/AdeudosComponent.vue';
export default {
    components: {TableTotals, TableRemisiones, TablePagos, RegPagoComponent, LoadComponent, AdeudosComponent},
    props: ['clienteid', 'role_id'],
    mixins: [formatNumber, toast, sweetAlert, moment],
    data(){
        return {
            datosCortes: {
                cliente_id: null,
                name: null,
                total: null,
                total_pagos: null,
                total_devolucion: null,
                total_pagar: null,
                cortes: []
            },
            form: {
                id: null, 
                cliente_id: null,
                remcliente_id: null, 
                corte_id: null,
                corte_id_favor: null,
                pago: null,
                fecha: null,
                nota: null,
                tipo: null
            },
            corte: {},
            load: false,
            ficticios: [],
            fieldsFic: [
                { key: 'created_at', label: 'Fecha de registro' },
                { key: 'ingresado_por', label: 'Ingresado por' },
                { key: 'corte', label: 'Corte' },
                { key: 'fecha', label: 'Fecha del pago' },
                'nota', 'pago',
            ],
            total_ficticios: 0,
            viewAdeudos: true
        }
    },
    created: function(){
        this.verPagos();
    },
    methods: {
        // PAGOS POR CLIENTE
        verPagos() {
            this.load = true;
            axios.get('/cortes/by_cliente', { params: { cliente_id: this.clienteid } }).then(response => {
                if (response.data.cortes.length > 0)
                    this.datosCortes = response.data;
                else
                    this.makeToast('warning', `El cliente no cuenta con cortes.`);
                this.load = false;
            }).catch(error => {
                this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                this.load = false;
            });
        },
        // REGRESAR A LA PANTALLA ANTERIOR
        goBack(){
            window.close();
        },
        // REGISTRAR PAGO DEL CORTE
        registrarPago(corte){
            this.corte = corte;
            this.form = {
                id: null, 
                cliente_id: corte.cliente_id,
                remcliente_id: null, 
                corte_id: corte.corte_id,
                corte_id_favor: null,
                pago: null,
                fecha: null,
                nota: null,
                tipo: null
            };
            this.$refs['modal-regPago'].show();
        },
        // PAGO GUARDADO
        savePayment(){
            this.$refs['modal-regPago'].hide();
            this.messageAlert('center', 'success', 'El pago se guardo correctamente', null, 'reload');
        },
        // OBTENER PAGOS FICTICIOS
        showFicticios() {
            this.load = true;
            axios.get('/cortes/by_ficticios', { params: { cliente_id: this.clienteid } }).then(response => {
                this.ficticios = response.data.remdepositos;
                if (this.ficticios.length > 0) {
                    this.total_ficticios = response.data.total;
                    this.$refs['modal-ficticios'].show();
                } else {
                    this.messageAlert('center', 'info', 'No hay registro de pagos ficticios', null, 'info');
                }
                this.load = false;
            }).catch(error => {
                this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                this.load = false;
            });
        },
        // AGREGAR AL SALDO DEUDOR
        addAdeudo(corte) {
            this.messageOptions("¿Agregar saldo pendiente a saldo deudor?", "SI", "NO")
                .then((result) => {
                    if (result.isConfirmed) {
                        this.messageOptions(`Saldo: $${numeral(corte.total_pagar).format("0,0[.]00")}`, "Confirmar", "Cancelar")
                        .then((result) => {
                            if (result.isConfirmed) {
                                this.saveAdeudo(corte);
                            }
                        });
                    }
                });
        },
        saveAdeudo(corte) {
            this.load = true;
            let form = {
                cliente_id: corte.cliente_id,
                corte_id: corte.corte_id,
                total_pagar: corte.total_pagar
            };
            axios.post('/cortes/adeudos/save', form).then(response => {
                this.messageAlert('center', 'success', 'El saldo se agrego correctamente a adeudos', null, 'reload');
                this.load = false;
            }).catch(error => {
                this.load = false;
            });
        }
    }
}
</script>