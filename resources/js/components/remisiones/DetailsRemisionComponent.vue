<template>
    <div>
        <!-- ENCABEZADO -->
        <b-row>
            <b-col><h5><b>Remisión {{ remision.id }}</b></h5></b-col>
            <!-- OMEGA BOOK / MODIFICAR CLIENTE_ID (ESTO ES DESDE MAJESTIC EDUCATION)-->
            <!-- <b-col v-if="remision.cliente_id == 288 && remision.cliente.name == 'OMEGA BOOK'" sm="2">
                <b-button v-if="(role_id === 1 || role_id === 2 || role_id == 6) && remision.envio == false && remision.estado != 'Cancelado'" 
                    variant="dark" pill block @click="enviarRemision()">
                    <i class="fa fa-send"></i> Enviar
                </b-button>
            </b-col> -->
            <b-col sm="2" class="text-right">
                <!-- <b-button v-if="(role_id === 1 || role_id == 2 || role_id == 3 || role_id == 6) && remision.total_pagar === remision.total && remision.estado != 'Cancelado' && remision.envio == false"
                    variant="dark" v-b-modal.modal-cancelar pill block>
                    <i class="fa fa-close"></i> Cancelar
                </b-button> -->
                <b-button v-if="(role_id === 1 || role_id == 2 || role_id == 3 || role_id == 6 || role_id == 10) && remision.total_pagar === remision.total && remision.estado != 'Cancelado'"
                    variant="dark" v-b-modal.modal-cancelar pill block>
                    <i class="fa fa-close"></i> Cancelar
                </b-button>
                <b-badge variant="danger" v-if="remision.estado == 'Cancelado'">Remisión cancelada</b-badge>
            </b-col>
            <b-col sm="2" class="text-right">
                <b-button v-if="role_id !== 4" v-b-modal.my-comentarios 
                    @click="ini_comment()" variant="dark" pill block>
                    <i class="fa fa-comment"></i> Comentarios
                </b-button>
            </b-col>
            <b-col sm="2" class="text-right">
                <b-button v-if="role_id === 1 || role_id === 2 || role_id == 3 || role_id == 6 || role_id == 10" 
                    :href="`/download_remision/${remision.id}`" variant="dark" pill block>
                    <i class="fa fa-download"></i> Remisión
                </b-button>
            </b-col>
            <b-col sm="2" class="text-right">
                <b-button v-b-toggle.collapse-1 variant="info" pill block>
                    <i class="fa fa-navicon"></i> Otros
                </b-button>
                <b-collapse id="collapse-1" class="mt-2">
                    <b-card>
                        <b-button v-if="role_id === 1 || role_id === 2 || role_id == 6 || role_id == 10" 
                            :href="`/codes/download_byremision/${remision.id}`" variant="dark" pill block>
                            <i class="fa fa-download"></i> Códigos
                        </b-button>
                        <b-button v-if="remision.paqueteria_id != null && remision.paqueteria_id != 0" 
                            variant="dark" pill block v-b-modal.modal-envio>
                            Paquetería
                        </b-button>
                    </b-card>
                </b-collapse>
            </b-col>
        </b-row>
        <hr>
        <datos-remision :remision="remision" :cliente_name="remision.cliente.name"></datos-remision>
        <!-- DATOS DE LA REMISION -->
        <b-table :items="remision.datos" :fields="fieldsDatos" responsive>
            <template v-slot:cell(costo_unitario)="row">
                ${{ row.item.costo_unitario | formatNumber }}
            </template>
            <template v-slot:cell(titulo)="row">
                {{ row.item.libro.titulo }}
                <b-badge v-if="row.item.pack_id != null" variant="info">scratch</b-badge>
            </template>
            <template v-slot:cell(total)="row">
                ${{ row.item.total | formatNumber }}
            </template>
            <template #thead-top="row">
                <tr>
                    <th colspan="4"></th>
                    <th><h5>${{ remision.total | formatNumber }}</h5></th>
                </tr>
            </template>
            <template #cell(codes)="row">
                <b-button v-if="row.item.codes.length > 0" 
                    @click="row.toggleDetails" size="sm" pill variant="dark">
                    {{ row.detailsShowing ? 'Ocultar' : 'Código(s)'}}
                </b-button>
            </template>
            <template #row-details="row">
                <b-row>
                    <b-col sm="3"></b-col>
                    <b-col sm="6">
                        <b-table :items="row.item.codes" :fields="fieldsCodes">
                            <template v-slot:cell(index)="data">
                                {{ data.index + 1 }}
                            </template>
                            <template v-slot:cell(devolucion)="data">
                                <b-badge v-if="!data.item.pivot.devolucion" variant="light">No</b-badge>
                                <b-badge v-else variant="warning">Si</b-badge>
                            </template>
                        </b-table>
                    </b-col>
                    <b-col sm="3"></b-col>
                </b-row>
            </template>
        </b-table>
        <br>
        <!-- PAGOS DE LA REMISION -->
        <div>
            <b-button v-if="remision.depositos.length > 0"
                variant="link" :class="mostrarPagos ? 'collapsed' : null"
                :aria-expanded="mostrarPagos ? 'true' : 'false'"
                aria-controls="collapse-3" @click="mostrarPagos = !mostrarPagos">
                <h4><b>Pagos</b></h4>
            </b-button>
            <b-collapse id="collapse-3" v-model="mostrarPagos" class="mt-2">
                <depositos-remision :depositos="remision.depositos"></depositos-remision>>
            </b-collapse>
        </div>
        <br><br>
        <!-- DEVOLUCIONES DE LA REMISION -->
        <div v-if="remision.total_devolucion >= 0 && unidades_devolucion > 0">
            <h4 style="color: #12013d"><b>Devolución</b></h4>
            <b-table :items="devoluciones" :fields="fieldsDevoluciones">
                <template v-slot:cell(costo_unitario)="row">
                    ${{ row.item.dato.costo_unitario | formatNumber }}
                </template>
                <template v-slot:cell(total)="row">
                    ${{ row.item.total | formatNumber }}
                </template>
                <template #thead-top="row">
                    <tr>
                        <th colspan="4"></th>
                        <th><h5>${{ remision.total_devolucion | formatNumber }}</h5></th>
                    </tr>
                </template>
                <template #cell(show_details)="row">
                    <b-button v-if="row.item.unidades > 0 && row.item.fechas.length > 0" size="sm" pill variant="info"
                        v-model="row.detailsShowing" @click="row.toggleDetails">
                        {{ row.detailsShowing ? 'Ocultar' : 'Detalles' }}
                    </b-button>
                </template>
                <template #row-details="row">
                    <b-card bg-variant="dark" text-variant="white">
                        <template #header>
                            <h6><b>Detalles</b></h6>
                        </template>
                        <b-table :items="row.item.fechas" :fields="fieldsFechas" dark>
                            <template v-slot:cell(total)="row">
                                ${{ row.item.total | formatNumber }}
                            </template>
                            <template v-slot:cell(creado_por)="row">
                                {{ row.item.creado_por != null ? row.item.creado_por : 'NA' }}
                            </template>
                            <template v-slot:cell(comentario)="row">
                                {{ row.item.comentario != null ? row.item.comentario:'NA' }}
                            </template>
                        </b-table>
                    </b-card>
                </template>
            </b-table>
        </div>
        <!-- MODALS -->
        <!-- CANCELAR REMISIÓN -->
        <b-modal id="modal-cancelar" title="Cancelar remisión">
            <p><b><i class="fa fa-exclamation-triangle"></i> ¿Estas seguro de cancelar la remisión?</b></p>
            <b-alert show variant="warning">
                <i class="fa fa-exclamation-circle"></i> Una vez presionado <b>OK</b> no se podrán realizar cambios.
            </b-alert>
            <div slot="modal-footer">
                <b-button variant="danger" :disabled="load" @click="cambiarEstado()">OK</b-button>
            </div>
        </b-modal>
        <!-- MODAL PARA HACER COMENTARIOS -->
        <b-modal id="my-comentarios" size="lg" title="Comentarios de la remisión">
            <div v-if="!newComment">
                <div class="text-right">
                    <b-button v-if="!newComment && remision.estado != 'Cancelado'" 
                        variant="success" @click="newComment = true">
                        <i class="fa fa-plus"></i> Agregar comentario
                    </b-button>
                </div>
                <hr>
                <b-table v-if="remision.comentarios.length" 
                    :items="remision.comentarios" :fields="fieldsComen">
                    <template v-slot:cell(index)="row">{{ row.index + 1 }}</template>
                    <template v-slot:cell(user_id)="row">{{ row.item.user.name }}</template>
                    <template v-slot:cell(created_at)="row">{{ row.item.created_at | moment }}</template> 
                </b-table>
                <b-alert v-else show variant="secondary">La remisión no tiene comentarios</b-alert>
            </div>
            <div v-else>
                <b-form @submit.prevent="guardarComentario()">
                    <label><b>Escribir comentario</b></label>
                    <b-form-input type="text" v-model="form.comentario" required></b-form-input><br>
                    <div class="text-right">
                        <b-button type="submit" :disabled="load" variant="success">
                            <i class="fa fa-check"></i> {{ !load ? 'Guardar' : 'Guardando' }} <b-spinner small v-if="load"></b-spinner>
                        </b-button>
                    </div>
                </b-form>
            </div>
            <div slot="modal-footer"></div>
        </b-modal>
        <!-- MODAL PARA MOSTRAR DETALLES DE ENVIO -->
        <b-modal id="modal-envio" title="Detalles de envió" hide-footer size="lg">
            <div v-if="remision.paqueteria_id != null && remision.paqueteria_id != 0">
                <details-paqueteria :tipo="remision"></details-paqueteria>
            </div>
        </b-modal>
    </div>
</template>

<script>
import formatNumber from '../../mixins/formatNumber';
import toast from '../../mixins/toast';
import moment from '../../mixins/moment';
import DatosRemision from './partials/DatosRemision.vue';
import DepositosRemision from './partials/DepositosRemision.vue';
import DetailsPaqueteria from '../funciones/paqueteria/DetailsPaqueteria.vue';
export default {
    props: ['remision', 'devoluciones', 'role_id'],
    components: {DatosRemision, DepositosRemision, DetailsPaqueteria},
    mixins: [formatNumber,toast,moment],
    data(){
        return {
            mostrarPagos: false,
            load: false,
            newComment: false,
            fieldsDatos: [
                { key: 'libro.ISBN', label: 'ISBN' },
                { key: 'titulo', label: 'Libro' },
                { key: 'costo_unitario', label: 'Costo unitario' },
                { key: 'unidades', label: 'Unidades' },
                { key: 'total', label: 'Total' },
                { key: 'codes', label: '' }
            ],
            fieldsCodes: [
                {key:'index', label:'N.'},
                {key:'codigo', label:'Código'},
                {key:'devolucion', label:'Devolución'},
            ],
            fieldsDevoluciones: [
                { key: 'libro.ISBN', label: 'ISBN' },
                { key: 'libro.titulo', label: 'Libro' },
                { key: 'costo_unitario', label: 'Costo unitario' },
                { key: 'unidades', label: 'Unidades' },
                { key: 'total', label: 'Total' },
                { key: 'show_details', label: '' }
                // { key: 'creado_por', label: 'Ingresado por' },
                // { key: 'fecha_devolucion', label: 'Fecha' },
                // { key: 'entregado_por', label: 'Entregada por' },
            ],
            fieldsFechas: [
                { key: 'created_at', label: 'Fecha' },
                { key: 'creado_por', label: 'Ingresado por' },
                { key: 'entregado_por', label: 'Entregado por' },
                { key: 'defectuosos', label: 'Defectuosos' },
                { key: 'comentario', label: 'Comentario' },
                { key: 'unidades', label: 'Unidades' },
                { key: 'total', label: 'Total' },
                // { key: 'pack_id', label: 'Scratch' },
            ],
            fieldsComen: [
                {key: 'index', label: 'N.'},
                'comentario',
                {key: 'user_id', label: 'Usuario'},
                {key: 'created_at', label: 'Fecha'},
            ],
            form: {
                remision_id: null,
                comentario: ''
            },
            mostrarDetalles: true,
            unidades_devolucion: 0
        }
    },
    created: function () {
        this.devoluciones.forEach(d => {
            this.unidades_devolucion += d.unidades;
        });
    },
    methods: {
        ini_comment(){
            this.newComment = false;
            this.form = {
                remision_id: null,
                comentario: ''
            }
        },
        cambiarEstado(){
            this.load = true;
            axios.put('/remisiones/cancel', this.remision).then(response => {
                this.remision.estado = response.data.estado;
                this.$bvModal.hide('modal-cancelar');
                this.load = false;
                this.makeToast('secondary', 'Remisión cancelada');
            })
            .catch(error => {
                this.load = false;
                this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
            });
        },
        guardarComentario(){
            this.form.remision_id = this.remision.id;
            this.load = true;
            axios.post('/guardar_comentario', this.form).then(response => {
                this.load = false;
                this.makeToast('success', 'El comentario se guardo correctamente');
                this.remision.comentarios.push(response.data);
                this.ini_comment();
            })
            .catch(error => {
                this.load = false;
                this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
            });
        }
    }
}
</script>

<style>

</style>