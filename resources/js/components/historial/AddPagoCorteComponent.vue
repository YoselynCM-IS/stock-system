<template>
    <div>
        <div v-if="!load">
            <!-- FUNCIONES (ENCABEZADO) -->
            <b-row>
                <b-col>
                    <h5><b>Cliente: {{ corte.cliente.name }}</b></h5>
                </b-col>
                <b-col sm="2" class="text-right">
                    <b-button variant="secondary" pill @click="goBack()">
                        <i class="fa fa-arrow-left"></i> Regresar
                    </b-button>
                </b-col>
            </b-row>
            <!-- DATOS DE LOS CORTES -->
            <div class="mb-3 mt-3">
                <b-row>
                    <b-col sm="2"><b>Temporada {{ corte.corte.tipo }}</b></b-col>
                    <b-col><b>{{ corte.corte.inicio }} - {{ corte.corte.final }}</b></b-col>
                    <b-col sm="2">
                        <b-button v-if="corte.total_pagar > 0" @click="registrarPago(corte)"
                            pill size="sm" variant="primary">
                            Realizar pago
                        </b-button>
                    </b-col>
                    <b-col sm="1" class="text-right">
                        <b-button :class="visible ? null : 'collapsed'" pill variant="info"
                            size="sm" :aria-expanded="visible ? 'true' : 'false'" block
                            aria-controls="collapse-1" @click="visible = !visible">
                            {{ !visible ? 'Mostrar':'Ocultar' }}
                        </b-button>
                        
                    </b-col>
                </b-row>
                <table-totals :dato="corte" :variant="'info'" :favor="true"></table-totals>
                <b-collapse id="collapse-1" v-model="visible" class="mt-2">
                    <table-pagos :cortePagar="corte.total_pagar"
                                :remdepositos="remdepositos" :role_id="role_id"
                                :cliente_id="corte.cliente_id" :showTitle="false"></table-pagos>
                </b-collapse>
            </div>
        </div>
        <!-- MODALS -->
        <b-modal ref="modal-regPago" title="Registrar pago" hide-footer>
            <reg-pago-component :form="form" :corte="corte" 
                    @savePayment="savePayment" :tipo="2"></reg-pago-component>
        </b-modal>
    </div>
</template>

<script>
import formatNumber from '../../mixins/formatNumber';
import TableTotals from '../funciones/TableTotals.vue';
import TablePagos from '../cortes/partials/TablePagos.vue';
import RegPagoComponent from '../cortes/partials/RegPagoComponent.vue';
import toast from '../../mixins/toast';
// import LoadComponent from '../partials/LoadComponent.vue';
import sweetAlert from '../../mixins/sweetAlert';
export default {
    // components: {TableTotals, TableRemisiones, TablePagos, RegPagoComponent, LoadComponent},
    // props: ['clienteid', 'role_id'],
    components: {TableTotals,TablePagos,RegPagoComponent},
    props: ['corte', 'remdepositos', 'role_id'],
    mixins: [formatNumber, toast, sweetAlert],
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
            },
            // corte: {},
            load: false,
            visible: true
        }
    },
    created: function(){
        // this.verPagos();
    },
    methods: {
        // PAGOS POR CLIENTE
        verPagos(){
            this.load = true;
            axios.get('/cortes/by_cliente', {params: {cliente_id: this.clienteid}}).then(response => {
                if(response.data.cortes.length > 0){
                    this.datosCortes = response.data;
                } else {
                    this.makeToast('warning', `El cliente no cuenta con cortes.`);
                }
                this.load = false;
            }).catch(error => {
                this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                this.load = false;
            });
        },
        // REGRESAR A LA PANTALLA ANTERIOR
        goBack(){
            // let ruta = '#';
            // if(this.role_id == 1) ruta = '/administrador/pagos'; // ADMINISTRADOR
            // if(this.role_id == 2) ruta = '/oficina/pagos'; // OFICINA
            // if(this.role_id == 6) ruta = '/manager/cortes/pagos'; // MANAGER
            window.close();
            // window.opener.document.location=ruta;
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
            };
            this.$refs['modal-regPago'].show();
        },
        // PAGO GUARDADO
        savePayment(){
            this.$refs['modal-regPago'].hide();
            this.messageAlert('center', 'success', 'El pago se guardo correctamente', null, 'reload');
        }
    }
}
</script>