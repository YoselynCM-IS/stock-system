<template>
    <div>
        <b-form @submit.prevent="onSubmit()">
            <datos-parte-1 :form="form" :load="load" :errors="errors"></datos-parte-1>
            <datos-parte-2 :form="form" :load="load" :errors="errors"></datos-parte-2>
            <b-row class="my-1">
                <b-col align="right">Teléfono (Oficina)</b-col>
                <div class="col-md-9">
                    <b-form-input 
                        id="input-telefono"
                        v-model="form.tel_oficina" 
                        :disabled="load"
                        required>
                    </b-form-input>
                    <div v-if="errors && errors.tel_oficina" class="text-danger">{{ errors.tel_oficina[0] }}</div>
                </div>
            </b-row>
            <hr>
            <div align="right">
                <b-button type="submit" :disabled="load" variant="success" pill>
                    <i class="fa fa-check"></i> {{ !load ? 'Guardar' : 'Guardando' }} <b-spinner small v-if="load"></b-spinner>
                </b-button>
            </div>
        </b-form>
    </div>
</template>

<script>
import DatosParte1 from './DatosParte1.vue';
import DatosParte2 from './DatosParte2.vue';
import catchError from '../../../mixins/catchError';
export default {
    components: { DatosParte1, DatosParte2 },
    mixins: [catchError],
    data(){
        return {
            form: {
                name: null,
                contacto: null,
                estado_id: null,
                email: null,
                telefono: null,
                tel_oficina: null
                // user_id: null
            }
        }
    },
    methods: {
        onSubmit(){
            this.load = true;
            this.errors = {};
            axios.post('/clientes/store_prospecto', this.form).then(response => {
                this.load = false;
                this.$emit('agregadoProspecto', response.data);
            }).catch(error => {
                this.catch_error(error);
            });
        }
    }
}
</script>

<style>

</style>