<template>
    <div>
        <b-form @submit.prevent="onSubmit">
            <b-form-group label="Nombre">
                <b-form-select v-model="form.tipo" :options="tipos" required
                    :disabled="load"
                ></b-form-select>
            </b-form-group>
            <b-form-group label="Fecha de inicio">
                <b-form-input 
                    type="date" v-model="form.inicio" 
                    :disabled="load">
                </b-form-input>
            </b-form-group>
            <b-form-group label="Fecha de termino">
                <b-form-input 
                    type="date" v-model="form.final" 
                    :disabled="load">
                </b-form-input>
            </b-form-group>
            <div class="text-right">
                <b-button type="submit" variant="success" pill :disabled="load">
                    <spinner-component :load="load"></spinner-component>
                </b-button>
            </div>
        </b-form>
    </div>
</template>

<script>
import SpinnerComponent from '../funciones/SpinnerComponent.vue';

export default {
    components: { SpinnerComponent },
    props: ['edit', 'form'],
    data(){
        return {
            load: false,
            tipos: [
                { value: null, text: 'Selecciona una opción' },
                { value: 'A', text: 'A' },
                { value: 'B', text: 'B' },
            ]
        }
    },
    methods: {
        // Guardar corte
        onSubmit(){
            this.load = true;
            if(!this.edit){
                axios.post('/cortes/store', this.form).then(response => {
                    this.$emit('saveCorte', response.data);
                    this.load = false;
                }).catch(error => {
                    this.load = false;
                });
            } else {
                axios.put('/cortes/update', this.form).then(response => {
                    this.$emit('saveCorte', response.data);
                    this.load = false;
                }).catch(error => {
                    this.load = false;
                });
            }
        }
    }
}
</script>

<style>

</style>