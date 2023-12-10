<template>
    <div>
        <div v-if="!load">
            <b-row>
                <b-col></b-col>
                <b-col sm="2">
                    <!-- <b-button v-if="misActs"
                        variant="success" pill size="sm" block 
                        @click="markCompleted()" class="mb-2"
                        :disabled="(selected.length == 0 || estado == 'completado' || loaded)">
                        <i class="fa fa-check-square-o"></i> Completado
                    </b-button> -->
                </b-col>
            </b-row>
            <b-table v-if="actividades.length > 0" striped 
                :items="actividades" :fields="fields" 
                :tbody-tr-class="rowClass">
                <template v-slot:cell(index)="row">
                    {{ row.index + 1 }}
                </template>
                <template v-slot:cell(fecha)="row">
                    {{ row.item.fecha | moment}}
                </template>
                <template v-slot:cell(show_details)="row">
                    <b-button variant="dark" pill size="sm" @click="row.toggleDetails">
                        {{ row.detailsShowing ? 'Ocultar' : 'Mostrar'}}
                    </b-button>
                </template>
                <template #row-details="row">
                    <b-card>
                        <b-row v-if="role_id != 7 && row.item.estado !== 'completado' && 
                                    row.item.estado !== 'cancelado'" class="mb-3">
                            <b-col sm="2">
                                <b-button v-if="!cancelarAct && !completarAct && !editarAct" variant="dark" 
                                    block pill size="sm" @click="editar(row.item)">
                                    <i class="fa fa-pencil"></i> Editar
                                </b-button>
                            </b-col>
                            <b-col sm="2">
                                <b-button v-if="!cancelarAct && !completarAct && !editarAct" variant="dark" 
                                    block pill size="sm" @click="cancelar(row.item)">
                                    <i class="fa fa-close"></i> Cancelar
                                </b-button>
                            </b-col>
                            <b-col sm="2">
                                <b-button v-if="!completarAct && !cancelarAct && !editarAct" variant="dark" 
                                    block pill size="sm" @click="markCompleted(row.item)">
                                    <i class="fa fa-check"></i> Completar
                                </b-button>
                            </b-col>
                            <b-col></b-col>
                            <b-col sm="1">
                                <b-button v-if="(completarAct || cancelarAct || editarAct) && (actividad.id == row.item.id)" 
                                    variant="secondary" block pill size="sm" @click="setValues(null, null, false, false, false)">
                                    <i class="fa fa-close"></i>
                                </b-button>
                            </b-col>
                        </b-row>
                        <mark-actividad-component v-if="completarAct && (actividad.id == row.item.id)" 
                            :actividad="actividad" @updatedActEstado="updatedActEstado"></mark-actividad-component>
                        <cancelar-actividad-component v-if="cancelarAct && (actividad.id == row.item.id)" 
                            :actividad="actividad" @updatedActEstado="updatedActEstado"></cancelar-actividad-component>
                        <editar-actividad-component v-if="editarAct && (actividad.id == row.item.id)" 
                            :actividad="actividad" @updatedActEstado="updatedActEstado"></editar-actividad-component>
                        <details-actividad :actividad="row.item"></details-actividad>
                    </b-card>
                </template>
                <template #cell(selected)="{ rowSelected }">
                    <template v-if="rowSelected">
                        <span aria-hidden="true">&check;</span>
                        <span class="sr-only">Selected</span>
                    </template>
                    <template v-else>
                        <span aria-hidden="true">&nbsp;</span>
                        <span class="sr-only">Not selected</span>
                    </template>
                </template>
            </b-table>
            <no-registros-component v-else></no-registros-component>  
        </div>
        <load-component v-else></load-component>
    </div>
</template>

<script>
import LoadComponent from '../../cortes/partials/LoadComponent.vue';
import MarkActividadComponent from './MarkActividadComponent.vue';
import CancelarActividadComponent from './CancelarActividadComponent.vue';
import EditarActividadComponent from './EditarActividadComponent.vue';
import DetailsActividad from './DetailsActividad.vue';
import formatFechaActs from '../../../mixins/formatFechaActs';
import sweetAlert from '../../../mixins/sweetAlert';
export default {
    props: ['actividades', 'load', 'role_id'],
    components: {LoadComponent, MarkActividadComponent, CancelarActividadComponent, EditarActividadComponent, DetailsActividad},
    mixins: [formatFechaActs, sweetAlert],
    data(){
        return {
            fields: [
                {key: 'index', label: 'N.'},
                {key: 'estado', label: 'Estado'},
                {key: 'tipo', label: 'Tipo'},
                {key: 'nombre', label: 'Actividad'},
                {key: 'fecha', label: 'Fecha'},
                {key: 'show_details', label: 'Detalles'}
            ],
            loaded: false,
            form: { selected: [] },
            actividad: {
                id: null,
                tipo: null,
                fecha: null,
                hora: null,
                estado: null,
                exitosa: 'PENDIENTE',
                observaciones: null
            },
            completarAct: false,
            cancelarAct: false,
            editarAct: false
        }
    },
    methods: {
        onRowSelected(items) {
            this.selected = items
        },
        editar(actividad){
            this.actividad.tipo = actividad.tipo;
            this.actividad.fecha = actividad.fecha.substring(0,10);
            this.actividad.hora = actividad.fecha.substring(11,28);
            this.setValues(actividad.id, null, false, false, true);
        },
        cancelar(actividad){
            this.setValues(actividad.id, 'cancelado', false, true, false);
        },
        markCompleted(actividad){
            this.setValues(actividad.id, 'completado', true, false, false);
        },
        updatedActEstado(actividad){
            this.messageAlert('center', 'success', 'La actividad se actualizo correctamente.', null, 'reload');
        },
        setValues(id, estado, completarAct, cancelarAct, editarAct){
            this.actividad.id = id;
            this.actividad.estado = estado;
            this.actividad.exitosa = 'PENDIENTE';
            this.actividad.observaciones = null;
            this.completarAct = completarAct;
            this.cancelarAct = cancelarAct;
            this.editarAct = editarAct;
        },
        // DISTINGUIR DE OTRO COLOR LAS ACTIVIDADES COMPLETADAS
        rowClass(item, type) {
            if (!item) return
            if (item.estado == 'completado') return 'table-success'
            if (item.estado == 'vencido') return 'table-warning'
            if (item.estado == 'cancelado') return 'table-danger'
            if (item.estado == 'proximo') return 'table-primary'
        },
    }
}
</script>

<style>

</style>