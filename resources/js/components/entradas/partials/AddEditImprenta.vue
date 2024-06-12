<template>
    <div>
        <b-form @submit.prevent="onSubmit">
            <b-form-group label="Nombre de la imprenta">
                <b-form-input v-model="form.imprenta" :disabled="load"
                    required autofocus style="text-transform:uppercase;">
                </b-form-input>
            </b-form-group>
            <b-form-group label="Tipo de libro">
                <b-form-select v-model="form.tipo" :options="tipos" required></b-form-select>
            </b-form-group>
            <button-save-component :load="load"></button-save-component>
        </b-form>
    </div>
</template>

<script>
import ButtonSaveComponent from '../../funciones/ButtonSaveComponent.vue';
export default {
  components: { ButtonSaveComponent },
    data(){
        return {
            load: false,
            form: {
                imprenta: null,
                tipo: null,
            },
            tipos: [
                { value: null, text: 'Selecciona una opción', disabled: true},
                { value: 'fisico', text: 'Físico' },
                { value: 'digital', text: 'Digital' }
            ],
        }
    },
    methods: {
        onSubmit(){
            this.load = true;
            axios.post('/entradas/save_imprenta', this.form).then(response => {
                this.$emit('saveImprenta', response.data);
                this.load = false;
            }).catch(error => {
                this.load = false;
            });
        }
    }
}
</script>

<style>

</style>