<template>
    <div>
        <b-form @submit.prevent="onSubmit">
            <b-form-group label="Temporada">
                <b-form-select v-model="form.corte_id" :options="options" required
                    :disabled="load"
                ></b-form-select>
            </b-form-group>
            <b-button type="submit" variant="success" pill :disabled="load" block>
                <spinner-component :load="load"></spinner-component>
            </b-button>
        </b-form>
    </div>
</template>

<script>
import SpinnerComponent from '../funciones/SpinnerComponent.vue';
export default {
  components: { SpinnerComponent },
    props: ['options', 'form', 'move'],
    data(){
        return {
            load: false
        }
    },
    methods: {
        onSubmit(){
            this.load = true;
            axios.put(`/cortes/${!this.move ? 'clasificar_rems':'move_rem'}`, this.form).then(response => {
                this.$emit('remsGuardadas', response.data);
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