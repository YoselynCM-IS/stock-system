<template>
    <div>
        <table class="table mb-2">
            <thead>
                <tr>
                    <th style="width: 60%;">Libro</th>
                    <th>Unidades</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td scope="col">
                        <busq-scratch-component @assignScracth="assignScracth"></busq-scratch-component>
                    </td>
                    <td scope="col">
                        <b-input v-model="temporalScratch.unidades" type="number" min="1" max="9999"></b-input>
                    </td>
                    <td scope="col">
                        <b-button variant="success" pill block @click="saveScratch()">
                            <i class="fa fa-level-down"></i>
                        </b-button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
import toast from '../../mixins/toast';
import busqScratchComponent from './scratch/busqScratchComponent.vue';
export default {
    components: { busqScratchComponent },
    mixins: [toast],
    data() {
        return {
            temporalScratch: {
                id: null,
                titulo: null,
                libro_fisico: null,
                libro_digital: null,
                unidades: 0,
            },
            datos: null
        }
    },
    methods: {
        // ASIGNAR DATOS DEL LIBRO SELECCIONADO
        assignScracth(libro) {
            this.temporalScratch.id = libro.id;
            this.temporalScratch.libro_fisico = libro.libro_fisico;
            this.temporalScratch.libro_digital = libro.libro_digital;
            this.temporalScratch.titulo = `PACK: ${libro.lf_titulo}`;
        },
        // GUARDAR PACK SELECCIONADO
        saveScratch() {
            this.$emit('addedScratch', this.temporalScratch);
        },
        // INICIALIZAR EL TEMPORAL SCRACTH
        ini_temporalScratch() {
            this.temporalScratch = {
                id: null,
                titulo: null,
                libro_fisico: null,
                libro_digital: null,
                piezas: 0,
                unidades: 0
            };
        },
    }
}
</script>

<style>

</style>