<template>
    <div>
        <b-form @submit.prevent="onSubmit">
            <b-form-group label="Nombre de la imprenta">
                <b-form-input v-model="form.imprenta" :disabled="load"
                    required autofocus style="text-transform:uppercase;">
                </b-form-input>
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
                imprenta: null
            }
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