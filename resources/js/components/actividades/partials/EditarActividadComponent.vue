<template>
    <div>
        <b-card class="mb-5" header="Editar actividad"
            border-variant="warning" header-bg-variant="warning" header-text-variant="white">
            <b-form @submit.prevent="onUpdate()">
                <b-form-group label="Añadir mas descripción">
                    <b-form-textarea v-model="actividad.observaciones" rows="3" max-rows="6"
                                :disabled="load" required></b-form-textarea>
                </b-form-group>
                <hr>
                <strong><i>Modificar fecha solo en caso de posponer la actividad</i></strong>
                <b-row class="mb-2">
                    <b-col sm="1"><label>Fecha</label></b-col>
                    <b-col>
                        <b-form-datepicker v-model="actividad.fecha" :disabled="load" required></b-form-datepicker>
                    </b-col>
                    <b-col sm="1" class="text-right">
                        <label v-if="actividad.tipo != 'nota'">Hora</label>
                    </b-col>
                    <b-col>
                        <b-form-timepicker v-if="actividad.tipo != 'nota'"
                            v-model="actividad.hora" locale="en" :disabled="load" required></b-form-timepicker>
                    </b-col>
                </b-row>
                <div class="mt-2 text-right">
                    <b-button type="submit" :disabled="load" variant="warning" pill class="text-white">
                        <i class="fa fa-check"></i> {{ !load ? 'Guardar' : 'Guardando' }} <b-spinner small v-if="load"></b-spinner>
                    </b-button>
                </div>
            </b-form>
        </b-card>
    </div>
</template>

<script>
export default {
    props: ['actividad'],
    data(){
        return {
            load: false
        }
    },
    methods: {
        onUpdate(){
            this.load = true;
            axios.put('/actividades/update', this.actividad).then(response => {
                this.$emit('updatedActEstado', true);
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