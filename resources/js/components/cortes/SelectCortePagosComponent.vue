<template>
    <div>
        <b-form>
            <b-form-group label="Temporada">
                <b-form-select v-model="form.corte_id" :options="options" required
                    :disabled="load"
                ></b-form-select>
            </b-form-group>
            <check-favor-component v-if="showVerify" @answerCheck="answerCheck"></check-favor-component>
            <div v-if="showYes">
                <b-form-group label="Temporada (A favor)">
                    <b-form-select v-model="form.corte_id_favor" :options="options_favor" required
                        :disabled="load"
                    ></b-form-select>
                </b-form-group>
            </div>
            <b-button v-if="!showYes" variant="success" pill :disabled="load" 
                @click="verify_totales" block>
                <spinner-component :load="load"></spinner-component>
            </b-button>
            <b-button v-else variant="success" pill :disabled="load" 
                @click="onSubmit" block>
                <spinner-component :load="load"></spinner-component>
            </b-button>
        </b-form>
    </div>
</template>

<script>
import SpinnerComponent from '../funciones/SpinnerComponent.vue';
import toast from '../../mixins/toast';
import CheckFavorComponent from '../funciones/CheckFavorComponent.vue';
import setCortes from '../../mixins/setCortes';
export default {
    components: { SpinnerComponent, CheckFavorComponent },
    mixins: [toast,setCortes],
    props: ['options', 'form', 'cortes', 'move'],
    data(){
        return {
            load: false,
            showVerify: false,
            showYes: false,
            options_favor: []
        }
    },
    methods: {
        // VERIFICAR TOTALES
        verify_totales(){
            this.load = true;
            axios.put('/cortes/verify_totales', this.form).then(response => {
                if(response.data == 1) this.onSubmit();
                if(response.data == 2) this.makeToast('warning', 'El pago no puede ser agregado a este corte ya que el total a pagar es $0');
                if(response.data == 3) this.showVerify = true;
                this.load = false;
            }).catch(error => {
                this.load = false;
                this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
            });
        },
        // GUARDAR PAGOS AL CORTE
        onSubmit(){
            this.load = true;
            axios.put(`/cortes/${!this.move ? 'clasificar_pagos':'move_pago'}`, this.form).then(response => {
                this.$emit('pagosGuardados', true);
                this.load = false;
            }).catch(error => {
                this.load = false;
            });
        },
        // RESPUESTA CORTE A FAVOR
        answerCheck(answer){
            if(answer == 'yes') {
                this.showVerify = false;
                this.showYes = true;
                this.options_favor = this.setCortes(this.cortes, this.form.corte_id);
            } else {
                this.$emit('pagosGuardados', false);
            }
        }
    }
}
</script>

<style>

</style>