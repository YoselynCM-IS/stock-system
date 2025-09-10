<template>
    <div>
        <b-table :items="adeudos" :fields="fields" 
            head-variant="dark" responsive class="mt-3">
            <template v-slot:cell(created_at)="row">
                {{ row.item.created_at | moment }}
            </template>
            <template v-slot:cell(saldo_inicial)="row">
                ${{ row.item.saldo_inicial | formatNumber }}
            </template>
            <template v-slot:cell(saldo_pagado)="row">
                ${{ row.item.saldo_pagado | formatNumber }}
            </template>
            <template v-slot:cell(saldo_pendiente)="row">
                ${{ row.item.saldo_pendiente | formatNumber }}
            </template>
            <template v-slot:cell(dias)="row">
                {{ row.item.dias | formatNumber }}
            </template>
            <template v-slot:cell(pagos)="row">
                <b-button @click="pagosAdeudos(row.item)"
                    variant="link" pill>
                    <b-icon-info-circle-fill></b-icon-info-circle-fill>
                </b-button>
            </template>
        </b-table>
        <!-- MODALS -->
        <b-modal ref="modal-details" title="Detalles adeudo" hide-footer size="lg">
            <div v-if="!load && adeudo != null">
                <b-row>
                    <b-col>
                        <h6><b>Temporada {{ adeudo.corte.tipo }}</b> {{ adeudo.corte.inicio }} - {{ adeudo.corte.final }}</h6>
                        <h6><b>Registrado por:</b> {{ adeudo.ingresado_por }}</h6>
                    </b-col>
                    <b-col>
                        <h6><b>Fecha de registro:</b> {{ adeudo.created_at | moment }}</h6>
                        <h6><b>Dias transcurridos</b> {{ adeudo.dias | formatNumber }}</h6>
                        <!-- <h6><b>Categoría:</b> {{ adeudo.rango.value }} días</h6> -->
                    </b-col>
                </b-row>
                <b-row v-if="adeudo.saldo_pendiente > 0" class="mb-2">
                    <b-col></b-col>
                    <b-col sm="2">
                        <b-button pill block variant="dark" size="sm" @click="addPago()">
                            <b-icon-plus-circle-fill></b-icon-plus-circle-fill> Pago
                        </b-button>
                    </b-col>
                </b-row>
                <b-table v-if="abonos.length > 0" class="mt-3"
                    :items="abonos" :fields="fieldsAbonos">
                    <template v-slot:cell(created_at)="row">
                        {{ row.item.created_at | momentDet }}
                    </template>
                    <template v-slot:cell(pago)="row">
                        ${{ row.item.pago | formatNumber }}
                    </template>
                    <template #thead-top="data">
                    <b-tr>
                        <b-th colspan="3"></b-th>
                        <b-th>${{ adeudo.saldo_pagado | formatNumber }}</b-th>
                    </b-tr>
                </template>
                </b-table>
                <no-registros-component v-else></no-registros-component>
            </div>
            <load-component v-else></load-component>
        </b-modal>
        <b-modal ref="modal-pago" title="Registrar pago" hide-footer>
            <b-form @submit.prevent="savePago()">
                <form-pago-component :form="form" :state="state" :load="load" :errors="errors"></form-pago-component>
                <b-row class="mt-2">
                    <b-col sm="4"></b-col>
                    <b-col sm="7" class="text-right">
                        <b-button type="submit" variant="success" :disabled="load" pill>
                            <i class="fa fa-check"></i> {{ !load ? 'Guardar' : 'Guardando' }} <b-spinner small v-if="load"></b-spinner>
                        </b-button>
                    </b-col>
                </b-row>
            </b-form>
        </b-modal>
    </div>
</template>

<script>
import formatNumber from '../../../mixins/formatNumber';
import moment from '../../../mixins/moment';
import sweetAlert from '../../../mixins/sweetAlert';
import toast from '../../../mixins/toast';
import FormPagoComponent from '../../funciones/FormPagoComponent.vue';
import NoRegistrosComponent from '../../funciones/NoRegistrosComponent.vue';
import LoadComponent from './LoadComponent.vue';
export default {
    components: { FormPagoComponent, NoRegistrosComponent, LoadComponent },
    props: ['adeudos', 'statusCurrency'],
    mixins: [formatNumber, moment, toast, sweetAlert],
    data() {
        return {
            fields: [
                { key: 'created_at', label: 'Fecha' },
                { key: 'dias', label: 'Dias' },
                { key: 'saldo_inicial', label: 'Saldo inicial' },
                { key: 'saldo_pagado', label: 'Saldo pagado' },
                { key: 'saldo_pendiente', label: 'Saldo pendiente' },
                { key: 'pagos', label: '' }
            ],
            fieldsAbonos: [
                { key: 'created_at', label: 'Fecha de registro' },
                { key: 'fecha', label: 'Fecha del pago' },
                { key: 'ingresado_por', label: 'Ingresado por' },
                { key: 'pago', label: 'Pago' },
                { key: 'nota', label: 'Concepto' }
            ],
            adeudo: null,
            load: false,
            state: null,
            form: {
                adeudo_id: null,
                pago: 0,
                fecha: null,
                nota: null
            },
            errors: {},
            abonos: []
        }
    },
    methods: {
        // MOSTRAR PAGOS DE ADUEDOS
        pagosAdeudos(adeudo) {
            this.load = true;
            axios.get('/cortes/adeudos/abonos/get', { params: { adeudo_id: adeudo.id,  statusCurrency: this.statusCurrency } }).then(response => {
                this.adeudo = adeudo;
                this.abonos = response.data;
                this.$refs['modal-details'].show();
                this.load = false;
            }).catch(error => {
                this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                this.load = false;
            });
        },
        // REGISTRAR PAGO
        addPago() {
            this.form.adeudo_id = this.adeudo.id;
            this.$refs['modal-pago'].show();
        },
        // GUARDAR ABONO
        savePago() {
            this.load = true;
            axios.post('/cortes/adeudos/abonos/save', this.form).then(response => {
                if (response.data.status) {
                    this.state = true;
                    this.messageAlert('center', 'success', response.data.message, null, 'reload');
                } else {
                    this.makeToast('warning', response.data.message);
                }

                this.errors = {};
                this.load = false;
            }).catch(error => {
                this.load = false;
                if (error.response.status === 422) {
                    this.errors = error.response.data.errors || {};
                    this.state = this.errors.pago ? false : true;
                } else
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
            });
        }
    }
}
</script>

<style>

</style>