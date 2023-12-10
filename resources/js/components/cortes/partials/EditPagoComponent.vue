<template>
    <div>
        <b-form @submit.prevent="updatePago()">
            <form-pago-component :form="form" :state="state" :load="load"></form-pago-component>
            <div class="text-right">
                <b-button type="submit" variant="success" :disabled="load" pill>
                    <i class="fa fa-check"></i> {{ !load ? 'Guardar' : 'Guardando' }} <b-spinner small v-if="load"></b-spinner>
                </b-button>
            </div>
        </b-form>
    </div>
</template>

<script>
import FormPagoComponent from '../../funciones/FormPagoComponent.vue';
import toast from '../../../mixins/toast';
export default {
    components: { FormPagoComponent },
    mixins: [toast],
    props: ['form'],
    data(){
        return {
            load: false,
            state: null
        }
    },
    methods: {
        updatePago(){
            if(this.form.pago >= 0){
                // if(this.form.pago <= this.form.corte_pagar){
                    this.state = true;
                    this.load = true; 
                    axios.put('/cortes/edit_payment', this.form).then(response => {
                        this.$emit('updPayment', response.data);
                        this.load = false;
                    }).catch(error => {
                        this.load = false;
                        this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                    });
                // }
                // else{
                //     this.state = false;
                //     this.makeToast('warning', 'El pago es mayor al total a pagar.');
                // }
            } else{
                this.state = false;
                this.makeToast('warning', 'El pago no puede ser menor a 0.');
            }
        }
    }
}
</script>

<style>

</style>