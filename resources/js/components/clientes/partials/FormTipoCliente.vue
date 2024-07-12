<template>
    <div>
        <b-form @submit.prevent="onSubmit()">
            <datos-parte-3 :form="form" :load="load"></datos-parte-3>
            <div align="right">
                <b-button type="submit" :disabled="load" variant="success">
                    <i class="fa fa-check"></i> {{ !load ? 'Guardar' : 'Guardando' }} <b-spinner small v-if="load"></b-spinner>
                </b-button>
            </div>
        </b-form>
    </div>
</template>

<script>
import DatosParte3 from './DatosParte3.vue';
import catchError from '../../../mixins/catchError';
    export default {
        props: ['cliente_id'],
        components: {DatosParte3},
        mixins: [catchError],
        data(){
            return {
                form: {
                    cliente_id: this.cliente_id,
                    tipo: null
                },
                load: false
            }
        },
        methods: {
            // GUARDAR SELECCION
            onSubmit(){
                this.load = true;
                axios.put('/clientes/update_tipo', this.form).then(response => {
                    this.load = false;
                    this.$emit('tipoUpdated', response.data);
                }).catch(error => {
                    this.catch_error(error);
                });
            }
        }
     }
</script>
                