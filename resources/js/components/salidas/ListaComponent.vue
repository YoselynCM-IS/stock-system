<template>
    <div>
        <div v-if="!showAdd && !showDetails">
            <!-- ENCABEZADO -->
            <b-row>
                <b-col>
                    <!-- PAGINACIÓN -->
                    <pagination size="default" :limit="1" :data="salidas" 
                        @pagination-change-page="getResults">
                        <span slot="prev-nav"><i class="fa fa-angle-left"></i></span>
                        <span slot="next-nav"><i class="fa fa-angle-right"></i></span>
                    </pagination>
                </b-col>
                <b-col sm="2" class="text-right">
                    <b-button v-if="(role_id === 1 || role_id == 2 || role_id == 6)"
                        variant="success" pill @click="addSalida()">
                        <i class="fa fa-plus"></i> Nueva salida
                    </b-button>
                </b-col>
            </b-row>
            <b-table v-if="!load" :items="salidas.data" :fields="fields"
                :tbody-tr-class="rowClass">
                <template v-slot:cell(index)="row">
                    {{ row.index + 1 }}
                </template>
                <template v-slot:cell(unidades)="row">
                    {{ row.item.unidades | formatNumber }}
                </template>
                <template v-slot:cell(created_at)="row">
                    {{ row.item.created_at | moment }}
                </template>
                <template v-slot:cell(details)="row">
                    <b-button variant="info" pill @click="getDetails(row.item)">
                        <i class="fa fa-info-circle"></i>
                    </b-button>
                </template>
                <template v-slot:cell(enviar)="row">
                    <b-button v-if="(role_id === 1 || role_id == 2 || role_id == 6) && row.item.estado == 'proceso'"
                        variant="success" pill @click="enviarSalida(row.item)">
                        <i class="fa fa-check-square-o"></i>
                    </b-button>
                </template>
            </b-table>
            <load-component v-else></load-component>
        </div>
        <div v-if="showAdd">
            <new-edit-salida-component></new-edit-salida-component>
        </div>
        <div v-if="showDetails">
            <b-row class="mb-2">
                <b-col><strong>Folio: </strong>{{ salida.folio }}</b-col>
                <b-col sm="2" class="text-right">
                    <b-button variant="dark" pill :href="`/salidas/download/${salida.id}`">
                        <i class="fa fa-download"></i> Descargar
                    </b-button>
                </b-col>
                <b-col sm="2" class="text-right">
                    <b-button variant="secondary" pill @click="showDetails = !showDetails">
                        <i class="fa fa-arrow-circle-left"></i> Regresar
                    </b-button>
                </b-col>
            </b-row>
            <b-table :items="salida.sregistros" :fields="fieldsRegistros">
                <template v-slot:cell(index)="row">
                    {{ row.index + 1 }}
                </template>
                <template v-slot:cell(unidades)="row">
                    {{ row.item.unidades | formatNumber }}
                </template>
                <template #thead-top="row">
                    <tr>
                        <th colspan="2"></th>
                        <th>{{ salida.unidades | formatNumber }}</th>
                    </tr>
                </template>
            </b-table>
            <div v-if="salida.saldevoluciones.length > 0" class="mt-5">
                <hr>
                <h5><b>DEVOLUCIONES</b></h5>
                <b-table :items="salida.saldevoluciones" :fields="fieldsDevoluciones">
                    <template v-slot:cell(index)="row">
                        {{ row.index + 1 }}
                    </template>
                    <template v-slot:cell(unidades)="row">
                        {{ row.item.unidades | formatNumber }}
                    </template>
                    <template v-slot:cell(created_at)="row">
                        {{ row.item.created_at | moment }}
                    </template>
                    <template #thead-top="row">
                        <tr>
                            <th colspan="2"></th>
                            <th>{{ salida.unidades_devolucion | formatNumber }}</th>
                        </tr>
                    </template>
                </b-table>
            </div>
        </div>
    </div>
</template>

<script>
import NewEditSalidaComponent from './NewEditSalidaComponent.vue';
import LoadComponent from '../funciones/LoadComponent.vue';
import moment from './../../mixins/moment';
import formatNumber from './../../mixins/formatNumber';
import sweetAlert from '../../mixins/sweetAlert';
export default {
  components: { NewEditSalidaComponent, LoadComponent },
  mixins: [moment, formatNumber, sweetAlert],
    props: ['role_id'],
    data(){
        return {
            showAdd: false,
            salidas: {},
            load: false,
            fields: [
                { key: 'index', label: 'N.' },
                { key: 'created_at', label: 'Fecha de creación' },
                { key: 'folio', label: 'Folio' },
                { key: 'unidades', label: 'Unidades' },
                { key: 'unidades_devolucion', label: 'Unidades (Devolución)' },
                { key: 'details', label: 'Detalles' },
                { key: 'enviar', label: 'Enviar' }
            ],
            showDetails: false,
            salida: {},
            fieldsRegistros: [
                { key: 'index', label: 'N.' },
                { key: 'libro.titulo', label: 'Libro' },
                { key: 'unidades', label: 'Unidades' },
            ],
            fieldsDevoluciones: [
                { key: 'index', label: 'N.' },
                { key: 'libro.titulo', label: 'Libro' },
                { key: 'unidades', label: 'Unidades' },
                { key: 'created_at', label: 'Fecha de la devolución' },
            ]
        }
    },
    created: function (){
        this.getResults();
    },
    methods: {
        // OBTENER TODAS LAS SALIDAS
        getResults(page = 1){
            this.load = true;
            axios.get(`/salidas/index?page=${page}`).then(response => {
                this.salidas = response.data;
                this.load = false;   
            }).catch(error => {
                this.load = false;
            });
        },
        // AGREGAR SALIDA
        addSalida(){
            this.showAdd = true;
        },
        // MOSTRAR DETALLES
        getDetails(salida){
            this.load = true;
            axios.get('/salidas/show', {params: { salida_id: salida.id }}).then(response => {
                this.salida = response.data;
                this.showDetails = true;
                this.load = false;   
            }).catch(error => {
                this.load = false;
            });
        },
        // MARCAR DE OTRO COLOR LAS SALIDAS QUE NO HAN SALIDO
        rowClass(item, type) {
            if (!item) return
            if (item.estado == 'proceso') return 'table-danger';
            if (item.estado == 'enviado') return 'table-success';
        },
        // ENVIAR A MAJESTIC LA SALIDA
        enviarSalida(salida){
            this.load = true;
            let form = { salida_id: salida.id };
            axios.put('/entradas/send_salida', form).then(response => {
                this.messageAlert('center', 'success', 'La entrada se creo correctamente.', null, 'reload');
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