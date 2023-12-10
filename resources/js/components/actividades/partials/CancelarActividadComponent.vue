<template>
    <div>
        <b-card class="mb-5" header="Cancelar actividad"
            border-variant="danger" header-bg-variant="danger" header-text-variant="white">
            <b-form @submit.prevent="onUpdate()">
                <b-form-group label="Motivo(s) para cancelar la actividad">
                    <b-form-textarea v-model="actividad.observaciones" rows="3" max-rows="6"
                                :disabled="load" required></b-form-textarea>
                </b-form-group>
                <div class="mt-2 text-right">
                    <b-button type="submit" :disabled="load" variant="danger" pill>
                        <i class="fa fa-check"></i> {{ !load ? 'Guardar' : 'Guardando' }} <b-spinner small v-if="load"></b-spinner>
                    </b-button>
                </div>
            </b-form>
        </b-card>
    </div>
</template>

<script>
import updEdoAct from '../../../mixins/updEdoAct';
export default {
    mixins: [updEdoAct]
}
</script>

<style>

</style>