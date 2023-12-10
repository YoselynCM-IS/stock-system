<template>
    <div>
        <b-form @submit.prevent="guardarPago()">
            <b-row>
                <b-col class="text-right" sm="4">
                    <label>Tipo de pago</label>
                </b-col>
                <b-col sm="7">
                    <b-form-select v-model="form.tipo" :options="tipos" required
                        :disabled="load"></b-form-select>
                </b-col>
            </b-row>
            <form-pago-component :form="form" :state="state" :load="load"></form-pago-component>
            <check-favor-component v-if="showVerify" @answerCheck="answerCheck"></check-favor-component>
            <div v-if="showYes">
                <b-form-group label="Temporada (A favor)">
                    <b-form-select v-model="form.corte_id_favor" :options="options" required
                        :disabled="load"
                    ></b-form-select>
                </b-form-group>
            </div>
            <br>
            <div class="text-right">
                <b-button type="submit" variant="success" :disabled="load" pill>
                    <i class="fa fa-check"></i> {{ !load ? 'Guardar' : 'Guardando' }} <b-spinner small v-if="load"></b-spinner>
                </b-button>
            </div>
        </b-form>
    </div>
</template>

<script>
import toast from '../../../mixins/toast';
import CheckFavorComponent from '../../funciones/CheckFavorComponent.vue';
import setCortes from '../../../mixins/setCortes';
import FormPagoComponent from '../../funciones/FormPagoComponent.vue';
export default {
  components: { CheckFavorComponent, FormPagoComponent },
    props: ['form', 'corte', 'tipo'],
    mixins: [toast,setCortes],
    data(){
        return {
            load: false,
            state: null,
            showVerify: false,
            showYes: false,
            options: [],
            tipos: [
                { value: null, text: 'Selecciona una opción', disabled: true },
                { value: 'real', text: 'Real' },
                { value: 'ficticio', text: 'Ficticio' }
            ]
        }
    },
    methods: {
        // GUARDAR PAGO
        guardarPago(){
            if(this.form.pago > 0){
                if((this.form.pago <= this.corte.total_pagar) || 
                    (this.showYes && this.form.corte_id_favor !== null)){
                    this.state = true;
                    this.load = true; 
                    if(this.tipo == 1){
                        this.tipo1_guardarPago();
                    } else {
                        this.tipo2_guardarPago();
                    }
                } else{
                    this.showVerify = true;
                }
            } else{
                this.state = false;
                this.makeToast('warning', 'El pago tiene que ser mayor a 0');
            }
        },
        tipo1_guardarPago(){
            axios.post('/cortes/save_payment', this.form).then(response => {
                this.$emit('savePayment', true);
                this.load = false;
            }).catch(error => {
                this.load = false;
                this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
            });
        },
        tipo2_guardarPago(){
            axios.post('/historial/save_payment', this.form).then(response => {
                this.$emit('savePayment', true);
                this.load = false;
            }).catch(error => {
                this.load = false;
                this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
            });
        },
        // RESPUESTA DE CORTE A FAVOR
        answerCheck(answer){
            this.showVerify = false;
            if(answer == 'yes') {
                this.load = true;
                this.options = [];
                axios.get('/cortes/get_all').then(response => {
                    this.options = this.setCortes(response.data, this.form.corte_id);
                    this.showYes = true;
                    this.state = true;
                    this.load = false;
                }).catch(error => {
                    this.load = false;
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                });
            } else {
                this.state = false;
                this.makeToast('warning', 'El pago tiene que ser menor o igual al total a pagar del corte');
            }
        }
    }
}
</script>

<style>

</style>