<template>
    <div>
        <b-form @submit.prevent="saveDefectuosos()">
            <b-form-group label="Número de piezas con algún defecto">
                <b-form-input v-model="form.defectuosos" :disabled="load">
                </b-form-input>
            </b-form-group>
            <b-form-group label="Motivo">
                <b-form-textarea v-model="form.motivo" :disabled="load"
                    rows="3" max-rows="6" required>
                </b-form-textarea>
            </b-form-group>
            <div class="text-right mt-2">
                <b-button :disabled="form.defectuosos <= 0"
                    variant="success" type="submit" pill>
                    <i class="fa fa-check"></i> Guardar
                </b-button>
            </div>
        </b-form>
    </div>
</template>

<script>
export default {
    data() {
        return {
            form: {
                defectuosos: 0,
                motivo: null
            },
            load: false
        }
    },
    methods: {
        saveDefectuosos() {
            this.$emit('saveDefectuosos', this.form);
        }
    }
}
</script>

<style>

</style>