<template>
    <div> 
        <form @submit="guardarDeposito" enctype="multipart/form-data">
            <form-pago-component :form="form" :state="state" :load="load"></form-pago-component>
            <b-row v-if="!edit">
                <b-col class="text-right" sm="4">
                    <label>Comprobante</label>
                </b-col>
                <b-col sm="7">
                    <subir-foto-component :disabled="load" :titulo="'Subir pago'" :allowExt="allowExt" @uploadImage="uploadImage"></subir-foto-component>
                </b-col>
            </b-row>
            <check-favor-component v-if="showVerify" @answerCheck="answerCheck"></check-favor-component>
            <div v-if="showYes">
                <b-form-group label="Temporada (A favor)">
                    <b-form-select v-model="form.corte_id_favor" :options="options" required
                        :disabled="load"
                    ></b-form-select>
                </b-form-group>
            </div>
            <b-row class="mt-2">
                <b-col sm="8">
                    <b-alert show variant="info">
                        <i class="fa fa-exclamation-circle"></i> 
                        Verificar el pago antes de presionar <b>Guardar</b>, ya que después no se podrán realizar cambios.
                    </b-alert>
                </b-col>
                <b-col sm="4" class="text-center">
                    <b-button type="submit" variant="success" :disabled="load">
                        <i class="fa fa-check"></i> {{ !load ? 'Guardar' : 'Guardando' }} 
                        <b-spinner small v-if="load"></b-spinner>
                    </b-button>
                </b-col>
            </b-row>
        </form>
    </div>
</template>

<script>
import FormPagoComponent from '../funciones/FormPagoComponent.vue';
import CheckFavorComponent from '../funciones/CheckFavorComponent.vue';
import toast from '../../mixins/toast';
import getCortesFavor from '../../mixins/getCortesFavor';
import SubirFotoComponent from '../funciones/SubirFotoComponent.vue';
export default {
    mixins: [toast, getCortesFavor],
    props: ['form', 'edit'],
    components: {FormPagoComponent, CheckFavorComponent, SubirFotoComponent},
    data(){
        return {
            state: null,
            showVerify: false,
            showYes: false,
            allowExt: /(\.jpg|\.jpeg|\.png)$/i
        }
    },
    methods: {
        guardarDeposito(e){
            e.preventDefault();
            if(this.form.pago > 0){
                if((this.form.pago <= this.form.total_pendiente) || 
                    (this.showYes && this.form.corte_id_favor !== null)){
                    this.load = true;
                    this.state = true;
                    let formData = new FormData();
                    formData.append('file', this.form.file, this.form.file.name);
                    formData.append('id', this.form.id);
                    formData.append('enteditoriale_id', this.form.enteditoriale_id);
                    formData.append('corte_id', this.form.corte_id);
                    formData.append('corte_id_favor', this.form.corte_id_favor);
                    formData.append('pago', this.form.pago);
                    formData.append('fecha', this.form.fecha);
                    formData.append('nota', this.form.nota);
                    formData.append('total_pendiente', this.form.total_pendiente);
                    axios.post('/entradas/save_pago', formData, { 
                        headers: { 'content-type': 'multipart/form-data' } })
                    .then(response => {
                        this.$emit('savedPago', response.data);
                        this.load = false;
                    }).catch(error => {
                        this.load = false;
                        this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                    });
                    // if(!this.edit){ } else {
                    //     axios.put('/entradas/update_pago', this.form).then(response => {
                    //         this.$emit('savedPago', response.data);
                    //         this.load = false;
                    //     }).catch(error => {
                    //         this.load = false;
                    //         this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                    //     });
                    // }
                } else {
                    this.showVerify = true;
                    // this.state = false;
                    // this.makeToast('warning', 'El monto es mayor al total pendiente');
                }
            } else {
                this.state = false;
                this.makeToast('warning', 'El monto tiene que ser mayor a 0');
            }
            // if(this.repayment.pago > 0){
            //     if(this.repayment.pago <= this.entrada.total_pendiente){
            //         this.state = null;
            //         this.load = true;
            //         axios.put('/pago_entrada', this.repayment).then(response => {
            //             this.makeToast('success', 'El pago se guardo correctamente');
            //             this.load = false;
            //             this.repayment = {
            //                 entrada_id: 0,
            //                 pago: null
            //             };
            //             this.$bvModal.hide('modal-registrarPago');
            //             this.entradas[this.posicion].total_pagos = response.data.total_pagos;
            //             this.acumular();

            //         }).catch(error => {
            //             this.load = false;
            //             this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
            //         });
            //     }
            //     else{
            //         this.state = false;
            //         this.makeToast('warning', 'El pago es mayor al total pendiente');
            //     }
            // }
            // else{
                // this.state = false;
                // this.makeToast('warning', 'El pago tiene que ser mayor a 0');
            // }
        },
        // RESPUESTA DE CORTE A FAVOR
        answerCheck(answer){
            this.showVerify = false;
            if(answer == 'yes') {
                this.getCortes(this.form.corte_id);
                this.showYes = true;
                this.state = true;
            } else {
                this.state = false;
                this.makeToast('warning', 'El pago tiene que ser menor o igual al total a pagar del corte');
            }
        },
        uploadImage(file){
            this.form.file = file;
        }
    }
}
</script>

<style>

</style>