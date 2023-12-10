<template>
    <div>
        <b-card class="mb-5" header="Completar actividad"
            border-variant="success" header-bg-variant="success" header-text-variant="white">
            <b-form @submit.prevent="onUpdate()">
                <b-form-group label="¿La actividad se completo con exito?">
                    <b-form-radio v-model="actividad.exitosa" name="exitosa" value="SI" required>
                        <h6><b-badge variant="success"><i class="fa fa-smile-o"></i></b-badge> SI</h6>
                    </b-form-radio>
                    <b-form-radio v-model="actividad.exitosa" name="exitosa" value="NO" required>
                        <h6><b-badge variant="warning"><i class="fa fa-frown-o"></i></b-badge> NO</h6>
                    </b-form-radio>
                    <b-form-radio v-model="actividad.exitosa" name="exitosa" value="REGULAR" required>
                        <h6><b-badge variant="secondary"><i class="fa fa-meh-o"></i></b-badge> REGULAR</h6> 
                    </b-form-radio>
                </b-form-group>
                <b-form-group label="Observaciones">
                    <b-form-textarea v-model="actividad.observaciones" rows="3" max-rows="6"
                                :disabled="load" required></b-form-textarea>
                </b-form-group>
                <div class="mt-2 text-right">
                    <b-button type="submit" :disabled="load" variant="success" pill>
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