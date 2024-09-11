<template>
    <div>
        <check-connection-component></check-connection-component>
        <div v-if="listadoPromociones">
            <b-row>
                <!-- BUSCAR PROMOCION POR FOLIO -->
                <b-col sm="3">
                    <b-row class="my-1">
                        <b-col sm="2">
                            <label for="input-folio">Folio</label>
                        </b-col>
                        <b-col sm="10">
                            <b-form-input style="text-transform:uppercase;" id="input-folio" v-model="folio"
                                @keyup.enter="porFolio()">
                            </b-form-input>
                        </b-col>
                    </b-row>
                </b-col>
                <!-- BUSCAR PROMOCION POR PLANTEL -->
                <b-col sm="5">
                    <b-row class="my-1">
                        <b-col sm="2">
                            <label for="input-plantel">Plantel</label>
                        </b-col>
                        <b-col sm="10">
                            <b-input style="text-transform:uppercase;" v-model="queryPlantel" @keyup="porPlantel()">
                            </b-input>
                        </b-col>
                    </b-row>
                </b-col>
                <!-- CREAR UNA PROMOCION -->
                <b-col sm="4">
                    <b-row>
                        <b-col sm="3">
                            <label for="input-inicio">De:</label>
                        </b-col>
                        <b-col sm="9">
                            <input class="form-control" type="date" :state="stateDate" v-model="inicio"
                                @change="porFecha()">
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col sm="3">
                            <label for="input-final">A: </label>
                        </b-col>
                        <b-col sm="9">
                            <input class="form-control" type="date" v-model="final" @change="porFecha()">
                        </b-col>
                    </b-row>
                </b-col>
            </b-row>
            <br>
            <b-row>
                <b-col>
                    <!-- PAGINACIÓN -->
                    <pagination size="default" :limit="1" :data="promotionsData" @pagination-change-page="getResults">
                        <span slot="prev-nav"><i class="fa fa-angle-left"></i></span>
                        <span slot="next-nav"><i class="fa fa-angle-right"></i></span>
                    </pagination>
                </b-col>
                <b-col class="text-right" v-if="role_id != 8 && role_id != 9">
                    <a v-if="promotions.length > 0" class="btn btn-dark"
                        :href="'/download_promotion/' + queryPlantel + '/' + inicio + '/' + final + '/general'">
                        <i class="fa fa-download"></i> General
                    </a>
                    <a v-if="promotions.length > 0 && (role_id === 1 || role_id === 2 || role_id == 6 || role_id == 10)"
                        class="btn btn-dark"
                        :href="'/download_promotion/' + queryPlantel + '/' + inicio + '/' + final + '/detallado'">
                        <i class="fa fa-download"></i> Detallado
                    </a>
                </b-col>
                <b-col sm="3" class="text-right">
                    <b-button v-if="role_id === 1 || role_id == 2 || role_id == 6 || role_id == 10" variant="success"
                        @click="registrarPromocion()">
                        <i class="fa fa-plus"></i> Registrar promoción
                    </b-button>
                </b-col>
            </b-row>
            <!-- LISTADO DE PROMOCIONES -->
            <div v-if="!load">
                <b-table v-if="promotions.length > 0" responsive :items="promotions" :fields="fields" id="my-table"
                    :tbody-tr-class="rowClass">
                    <template v-slot:cell(index)="row">{{ row.index + 1 }}</template>
                    <template v-slot:cell(created_at)="row">{{ row.item.created_at | moment }}</template>
                    <template v-slot:cell(detalles)="row">
                        <b-button variant="info" pill size="sm" @click="detallesPromotion(row.item)">
                            <i class="fa fa-info"></i>
                        </b-button>
                    </template>
                    <template v-slot:cell(paqueteria)="row">
                        <b-button
                            v-if="(role_id === 1 || role_id == 6 || role_id === 2 || role_id == 10) && row.item.paqueteria_id == null && row.item.estado !== 'Cancelado'"
                            variant="dark" pill size="sm" @click="selectPaqueteria(row.item, row.index)">
                            <i class="fa fa-truck"></i>
                        </b-button>
                    </template>
                    <template v-slot:cell(devolucion)="row">
                        <!-- OMEGA BOOK / MODIFICAR CLIENTE_ID (ESTO ES DESDE MAJESTIC EDUCATION)-->
                        <!-- <b-button v-if="(row.item.cliente_id !== 288 && row.item.plantel !== 'OMEGA BOOK') 
                            && row.item.estado == 'Enviado' && row.item.unidades_pendientes > 0 &&
                            (role_id === 1 || role_id == 2 || role_id == 6)"
                            variant="primary" pill  @click="registrarDevolucion(row.item)">
                            Devolución
                        </b-button> -->
                        <b-button v-if="row.item.estado == 'Enviado' && row.item.unidades_pendientes > 0 &&
                            (role_id === 1 || role_id == 2 || role_id == 6 || role_id == 10)" variant="primary" pill
                            @click="registrarDevolucion(row.item)">
                            Devolución
                        </b-button>
                    </template>
                    <template #thead-top="row">
                        <tr>
                            <th colspan="6"></th>
                            <th>{{ total_unidades | formatNumber }}</th>
                            <th colspan="2"></th>
                        </tr>
                    </template>
                </b-table>
                <div v-else>
                    <br><b-alert show variant="dark"><i class="fa fa-warning"></i> No se encontraron registros</b-alert>
                </div>
            </div>
            <div v-else class="text-center text-info my-2 mt-3">
                <b-spinner class="align-middle"></b-spinner>
                <strong>Cargando...</strong>
            </div>
        </div>
        <!-- MOSTRAR DETALLES DE LA PROMOCIÓN -->
        <div v-if="mostrarDetalles">
            <b-row>
                <b-col sm="2">
                    <h6><b>Folio</b>: {{ promotion.folio }}</h6>
                    <h6><b>Fecha</b>: {{ promotion.created_at | moment }}</h6>
                </b-col>
                <b-col>
                    <h6 v-if="promotion.entregado_por !== null">
                        <b>Responsable de la entrega:</b> {{ promotion.entregado_por }}
                    </h6>
                    <h6 v-if="promotion.creado_por !== null">
                        <b>Creado por:</b> {{ promotion.creado_por }}
                    </h6>
                </b-col>
                <!-- OMEGA BOOK / MODIFICAR CLIENTE_ID (ESTO ES DESDE MAJESTIC EDUCATION)-->
                <!-- <b-col v-if="promotion.cliente_id == 288 && promotion.plantel == 'OMEGA BOOK'" sm="2">
                    <b-button v-if="(role_id === 1 || role_id === 2 || role_id == 6) && promotion.envio == false && promotion.estado != 'Cancelado'" 
                        variant="dark" pill block @click="enviarPromotion()">
                        <i class="fa fa-send"></i> Enviar
                    </b-button>
                </b-col> -->
                <b-col sm="2">
                    <b-button v-if="promotion.paqueteria_id > 0" variant="dark" pill block v-b-modal.modal-envio-promo>
                        <i class="fa fa-truck"></i> Paquetería
                    </b-button>
                </b-col>
                <b-col sm="2" align="right">
                    <b-button variant="secondary" pill block
                        @click="listadoPromociones = true; mostrarDetalles = false;">
                        <i class="fa fa-mail-reply"></i> Regresar
                    </b-button>
                </b-col>
            </b-row>
            <b-row>
                <b-col>
                    <h6><b>Plantel</b>: {{ promotion.plantel }}</h6>
                    <h6 v-if="promotion.descripcion.length > 0"><b>Descripción</b>: {{ promotion.descripcion }}</h6>
                </b-col>
                <b-col sm="2" v-if="role_id != 8 && role_id != 9">
                    <b-button variant="dark" pill block :href="`/download_promocion/${promotion.id}`">
                        <i class="fa fa-download"></i> Descargar
                    </b-button>
                </b-col>
                <b-col sm="2" v-if="role_id != 8 && role_id != 9">
                    <!-- <b-button v-if="promotion.estado == 'Enviado' && promotion.unidades_devolucion == 0 && promotion.envio == false"
                        variant="danger" pill block v-b-modal.modal-cancel>
                        <i class="fa fa-close"></i> Cancelar
                    </b-button> -->
                    <b-button v-if="(role_id == 1 || role_id == 2 || role_id == 6 || role_id == 10) &&
                        (promotion.estado == 'Enviado' && promotion.unidades_devolucion == 0)" variant="danger" pill
                        block v-b-modal.modal-cancel>
                        <i class="fa fa-close"></i> Cancelar
                    </b-button>
                </b-col>
            </b-row>
            <b-table class="mt-2" :items="promotion.departures" :fields="fieldsD">
                <template v-slot:cell(index)="row">{{ row.index + 1 }}</template>
                <template v-slot:cell(ISBN)="row">{{ row.item.libro.ISBN }}</template>
                <template v-slot:cell(titulo)="row">{{ row.item.libro.titulo }}</template>
                <template #thead-top="row">
                    <tr>
                        <th colspan="3"></th>
                        <th>{{ promotion.unidades }}</th>
                        <th></th>
                    </tr>
                </template>
                <template #cell(show_details)="row">
                    <b-button v-if="row.item.libro.type == 'digital'" size="sm" @click="row.toggleDetails"
                        variant="info" pill>
                        Códigos
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
                            </b-table>
                        </b-col>
                        <b-col sm="3"></b-col>
                    </b-row>
                </template>
            </b-table>
            <div class="mt-5" v-if="promotion.prodevoluciones.length > 0">
                <hr>
                <h4><b>Devoluciones</b></h4>
                <b-table class="mt-2" :items="promotion.prodevoluciones" :fields="fieldsPD">
                    <template v-slot:cell(index)="row">{{ row.index + 1 }}</template>
                    <template v-slot:cell(ISBN)="row">{{ row.item.libro.ISBN }}</template>
                    <template v-slot:cell(titulo)="row">{{ row.item.libro.titulo }}</template>
                    <template v-slot:cell(created_at)="row">{{ row.item.created_at | moment }}</template>
                </b-table>
            </div>
        </div>
        <!-- CREAR UNA PROMOCIÓN -->
        <div v-if="mostrarRegistrar">
            <b-row>
                <b-col sm="6">
                    <h4 style="color: #170057">Registrar promoción</h4>
                </b-col>
                <b-col sm="3" align="right">
                    <b-button variant="success" @click="confirmarPromocion()" :disabled="load">
                        <i class="fa fa-check"></i> {{ !load ? 'Guardar' : 'Guardando' }} <b-spinner small
                            v-if="load"></b-spinner>
                    </b-button>
                </b-col>
                <b-col sm="3" align="right">
                    <b-button variant="secondary" @click="listadoPromociones = true; mostrarRegistrar = false;">
                        <i class="fa fa-mail-reply"></i> Regresar
                    </b-button>
                </b-col>
            </b-row>
            <hr>
            <b-row>
                <b-col>
                    <b-row>
                        <b-col sm="3"><label><b>Cliente</b>: <b id="txtObligatorio">*</b></label></b-col>
                        <b-col>
                            <b-input v-model="queryCliente" @keyup="mostrarClientes('activo')" autofocus
                                style="text-transform:uppercase;" :disabled="load" required :state="state">
                            </b-input>
                            <div class="list-group" v-if="clientes.length" id="listP">
                                <a href="#" v-bind:key="i" class="list-group-item list-group-item-action"
                                    v-for="(cliente, i) in clientes" @click="selectCliente(cliente)">
                                    {{ cliente.name }}
                                </a>
                            </div>
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col sm="3"><label><b>Descripción (Opcional)</b>:</label></b-col>
                        <b-col>
                            <b-input style="text-transform:uppercase;" type="text"
                                v-model="promocion.descripcion"></b-input>
                        </b-col>
                    </b-row>
                </b-col>
                <b-col>
                    <b-row>
                        <b-col sm="6"><label><b>Responsable de la entrega</b>: <b
                                    id="txtObligatorio">*</b></label></b-col>
                        <b-col>
                            <b-form-select :state="stateResp" v-model="promocion.entregado_por"
                                :options="options"></b-form-select>
                        </b-col>
                    </b-row>
                </b-col>
            </b-row>
            <hr>
            <b-table :items="registros" :fields="fieldsR">
                <template v-slot:cell(index)="row">{{ row.index + 1 }}</template>
                <template v-slot:cell(ISBN)="row">{{ row.item.ISBN }}</template>
                <template v-slot:cell(titulo)="row">{{ row.item.titulo }}</template>
                <template v-slot:cell(eliminar)="row">
                    <b-button variant="danger" @click="eliminarRegistro(row.index)">
                        <i class="fa fa-minus-circle"></i>
                    </b-button>
                </template>
                <template #thead-top="row">
                    <tr>
                        <th colspan="1"></th>
                        <th>ISBN</th>
                        <th>Libro</th>
                        <th></th>
                        <th>Unidades</th>
                    </tr>
                    <tr>
                        <th colspan="1"></th>
                        <th>
                            <b-input id="input-isbn" autofocus v-model="temporal.ISBN" @keyup.enter="buscarLibroISBN()"
                                v-if="inputISBN" :disabled="load"></b-input>
                            <label v-if="!inputISBN">{{ temporal.ISBN }}</label>
                        </th>
                        <th>
                            <b-input style="text-transform:uppercase;" id="input-libro" autofocus
                                v-model="temporal.titulo" @keyup="mostrarLibros()" v-if="inputLibro" :disabled="load">
                            </b-input>
                            <div class="list-group" v-if="resultslibros.length" id="listaBL">
                                <a href="#" v-bind:key="i" class="list-group-item list-group-item-action"
                                    v-for="(libro, i) in resultslibros" @click="datosLibro(libro)">
                                    {{ libro.titulo }}
                                </a>
                            </div>
                            <label v-if="!inputLibro">{{ temporal.titulo }}</label>
                        </th>
                        <th>
                            <b-form-select v-if="temporal.type == 'digital'" v-model="temporal.tipo"
                                :options="code_tipos" required :disabled="load"
                                @change="checkDisponible()"></b-form-select>
                        </th>
                        <th>
                            <b-form-input id="input-unidades" autofocus @keyup.enter="guardarRegistro()"
                                v-if="inputUnidades" v-model="temporal.unidades" type="number" required
                                :disabled="load">
                            </b-form-input>
                        </th>
                        <th>
                            <b-button variant="secondary" @click="eliminarTemporal()" v-if="inputUnidades"
                                :disabled="load">
                                <i class="fa fa-minus-circle"></i>
                            </b-button>
                        </th>
                    </tr>
                    <tr>
                        <th colspan="4"></th>
                        <th>{{ unidades_crear | formatNumber }}</th>
                        <th></th>
                    </tr>
                </template>
            </b-table>
            <!-- RESUMEN DE LA PROMOCIÓN -->
            <b-modal ref="modal-confirmar-promocion" size="xl" title="Resumen de la promocion">
                <label>
                    <b>Plantel: </b><label style="text-transform:uppercase;">{{ promocion.plantel }}</label>
                </label><br>
                <label v-if="promocion.descripcion !== ''">
                    <b>Descripción: </b><label style="text-transform:uppercase;">{{ promocion.descripcion }}</label><br>
                </label><br>
                <label><b>Responsable de la entrega: </b> {{ promocion.entregado_por }}
                </label>
                <b-table :items="registros" :fields="fieldsR">
                    <template v-slot:cell(index)="row">{{ row.index + 1 }}</template>
                    <template v-slot:cell(ISBN)="row">{{ row.item.ISBN }}</template>
                    <template v-slot:cell(titulo)="row">{{ row.item.titulo }}</template>
                    <template #thead-top="row">
                        <tr>
                            <th colspan="4"></th>
                            <th>{{ unidades_crear | formatNumber }}</th>
                        </tr>
                    </template>
                </b-table>
                <div slot="modal-footer">
                    <b-row>
                        <b-col sm="10">
                            <b-alert show variant="info">
                                <i class="fa fa-exclamation-circle"></i>
                                <b>Verificar los datos de la promoción.</b> En caso de algún error, modificar antes de
                                presionar <b>Confirmar</b> ya que después no se podrán realizar cambios.
                            </b-alert>
                        </b-col>
                        <b-col sm="2" align="right">
                            <b-button variant="success" @click="guardarPromocion()" :disabled="load">
                                <i class="fa fa-check"></i> Confirmar
                            </b-button>
                        </b-col>
                    </b-row>
                </div>
            </b-modal>
        </div>
        <!-- REGISTRAR DEVOLUCION -->
        <div v-if="mostrarDevolucion">
            <!-- INGRESAR UNIDADES PARA LA DEVOLUCION -->
            <div>
                <b-row>
                    <b-col sm="2" class="text-left">
                        <b-button variant="secondary" pill
                            @click="mostrarDevolucion = false; listadoPromociones = true;">
                            <i class="fa fa-arrow-circle-left"></i> Regresar
                        </b-button>
                    </b-col>
                    <b-col>
                        <h6><b>REGISTRAR DEVOLUCIÓN</b></h6>
                        <b>Folio: </b> {{ formDev.folio }}
                    </b-col>
                    <b-col sm="2" class="text-right">
                        <b-button variant="success" pill v-b-modal="'confirmDevolucion'"
                            :disabled="load || formDev.unidades_devolucion <= 0">
                            <i class="fa fa-check"></i> Guardar
                        </b-button>
                    </b-col>
                </b-row>
                <hr>
                <b-table :items="formDev.departures" :fields="fieldsD">
                    <template v-slot:cell(index)="row">{{ row.index + 1 }}</template>
                    <template v-slot:cell(unidades)="row">
                        <b-form-input v-if="row.item.type != 'digital'" :id="`inpDev-${row.index}`" type="number"
                            required :disabled="load" @change="checkUnidades(row.item, row.index)"
                            v-model="row.item.unidades">
                        </b-form-input>
                    </template>
                    <template #thead-top="row">
                        <th colspan="3"></th>
                        <th colspan="3">{{ formDev.unidades_devolucion | formatNumber }}</th>
                    </template>
                </b-table>
            </div>
            <!-- CONFIRMAR LA DEVOLUCION -->
            <b-modal id="confirmDevolucion" size="xl" title="Resumen de la devolución">
                <h6><b>Folio: </b> {{ formDev.folio }}</h6>
                <b-table :items="formDev.departures" :fields="fieldsD">
                    <template v-slot:cell(index)="row">{{ row.index + 1 }}</template>
                    <template v-slot:cell(unidades)="row">{{ row.item.unidades | formatNumber }}</template>
                    <template #thead-top="row">
                        <th colspan="3"></th>
                        <th colspan="3">{{ formDev.unidades_devolucion | formatNumber }}</th>
                    </template>
                </b-table>
                <div slot="modal-footer">
                    <b-row>
                        <b-col sm="10">
                            <b-alert show variant="info">
                                <i class="fa fa-exclamation-circle"></i>
                                <b>Verificar los datos de la devolución.</b> En caso de algún error, modificar antes de
                                presionar <b>Confirmar</b> ya que después no se podrán realizar cambios.
                            </b-alert>
                        </b-col>
                        <b-col sm="2" align="right">
                            <b-button pill variant="success" @click="guardarDevolucion()" :disabled="load">
                                <i class="fa fa-check"></i> Confirmar
                            </b-button>
                        </b-col>
                    </b-row>
                </div>
            </b-modal>
        </div>
        <!-- MODALS -->
        <b-modal id="modal-cancel" title="Cancelar promoción">
            <p><b><i class="fa fa-exclamation-triangle"></i> ¿Estas seguro de cancelar la promoción?</b></p>
            <b-alert show variant="warning">
                <i class="fa fa-exclamation-circle"></i> Una vez presionado <b>OK</b> no se podrán realizar cambios.
            </b-alert>
            <div slot="modal-footer">
                <b-button variant="danger" :disabled="load" pill @click="cancel_promotion()">
                    OK
                </b-button>
            </div>
        </b-modal>
        <!-- SELECCIONAR INFORMACIÓN DE PAQUETERIA -->
        <b-modal ref="modalPaqueteria" id="modal-paqueteria" title="Información de paquetería" size="lg" hide-footer>
            <envio-paqueteria :enlace_id="promotion_id" :ruta="'/promotions/save_envio'" :tipo="'promoción'"
                @savedEnvio="savedEnvio"></envio-paqueteria>
        </b-modal>
        <!-- MODAL PARA MOSTRAR DETALLES DE ENVIO -->
        <b-modal id="modal-envio-promo" title="Detalles de envió" hide-footer size="lg">
            <details-paqueteria :tipo="promotion"></details-paqueteria>
        </b-modal>
    </div>
</template>

<script>
import setResponsables from '../../mixins/setResponsables';
import getLibros from '../../mixins/getLibros';
import searchCliente from '../../mixins/searchCliente';
import sweetAlert from '../../mixins/sweetAlert';
import EnvioPaqueteria from '../funciones/paqueteria/EnvioPaqueteria.vue';
import DetailsPaqueteria from '../funciones/paqueteria/DetailsPaqueteria.vue';
    export default {
    components: { EnvioPaqueteria, DetailsPaqueteria },
        props: ['role_id'],
        mixins: [setResponsables, getLibros, searchCliente, sweetAlert],
        data() {
            return {
                listadoPromociones: true,
                mostrarRegistrar: false,
                promotions: [],
                fields: [
                    {key: 'index', label: 'N.'}, 
                    'folio', 
                    {key: 'created_at', label: 'Fecha de creación'},
                    'plantel', 
                    {key: 'unidades', label: 'Unidades (Salida)'}, 
                    {key: 'unidades_devolucion', label: 'Unidades (Devolucion)'}, 
                    {key: 'unidades_pendientes', label: 'Unidades restantes'}, 
                    { key: 'detalles', label: '' },
                    { key: 'devolucion', label: '' },
                    { key: 'paqueteria', label: '' }
                ],
                load: false,
                registros: [],
                fieldsR: [
                    {key: 'index', label: 'N.'}, 
                    {key: 'ISBN', label: 'ISBN'}, 
                    {key: 'titulo', label: 'Libro'}, 
                    {key: 'tipo', label: ''},
                    'unidades', 
                    {key: 'eliminar', label: ''}
                ],
                fieldsD: [
                    {key: 'index', label: 'N.'}, 
                    {key: 'ISBN', label: 'ISBN'}, 
                    {key: 'titulo', label: 'Libro'}, 
                    'unidades',
                    {key: 'show_details', label: ''}, 
                ],
                fieldsPD: [
                    {key: 'index', label: 'N.'}, 
                    {key: 'ISBN', label: 'ISBN'}, 
                    {key: 'titulo', label: 'Libro'}, 
                    'unidades',
                    {key: 'created_at', label: 'Fecha de la devolución'},
                    {key: 'creado_por', label: 'Ingresado por'}
                ],
                options: [],
                temporal: {
                    id: 0,
                    type: null,
                    ISBN: '',
                    titulo: '',
                    tipo: null,
                    unidades: null,
                    piezas: 0,
                    codigos: 0
                },
                promocion: {
                    id: null,
                    folio: '',
                    cliente_id: null,
                    plantel: '',
                    descripcion: '',
                    unidades: 0,
                    created_at: '',
                    departures: [],
                    entregado_por: null,
                    estado: null
                },
                promotion: {},
                inputISBN: true,
                inputLibro: true,
                inputUnidades: false,
                state: null,
                mostrarDetalles: false,
                folio: null,
                queryPlantel: null,
                perPage: 10,
                currentPage: 1,
                loadRegisters: false,
                stateDate: null,
                inicio: '0000-00-00',
                final: '0000-00-00',
                total_unidades: 0,
                unidades_crear: 0,
                stateResp: null,
                mostrarDevolucion: false,
                formDev: {
                    id: null,
                    folio: null,
                    unidades_devolucion: 0,
                    departures: []
                },
                promotionsData: {},
                searchPlantel: false,
                searchFecha: false,
                code_tipos: [
                    {value: null, text: 'Seleccionar', disabled: true},
                    {value: 'demo', text: 'demo'},
                    {value: 'profesor', text: 'profesor'}
                ],
                fieldsCodes: [
                    {key:'index', label:'N.'},
                    {key:'tipo', label:'Tipo'},
                    {key:'codigo', label:'Código'}
                ],
                position: null,
                promotion_id: null
            }
        },
        filters: {
            moment: function (date) {
                return moment(date).format('DD-MM-YYYY');
            },
            formatNumber: function (value) {
                return numeral(value).format("0,0[.]00"); 
            }
        },
        mounted: function(){
            this.getResults();
        },
        methods: {
            getResults(page = 1){
                if(!this.searchPlantel && !this.searchFecha) this.http_promociones(page);
                if(this.searchPlantel) this.http_plantel(page);
                if(this.searchFecha) this.http_fecha(page);
            },
            http_promociones(page = 1){
                this.load = true;
                axios.get(`/promotions/index?page=${page}`).then(response => {
                    this.promotionsData = response.data; 
                    this.promotions = response.data.data;
                    this.acumular_unidades();
                    this.load = false;   
                }).catch(error => {
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                    this.load = false;
                });
            },
            // BUSQUEDA POR FECHA
            porFecha(){
                if(this.final != '0000-00-00'){
                    if(this.inicio != '0000-00-00'){
                        this.http_fecha();
                    } else {
                        this.stateDate = false;
                        this.makeToast('warning', 'Es necesario seleccionar la fecha de inicio');
                    }
                }
            },
            http_fecha(page = 1){
                this.load = true;
                axios.get(`/buscar_fecha_promo?page=${page}`, {
                    params: {inicio: this.inicio, final: this.final, plantel: this.queryPlantel}}).then(response => {
                    this.promotionsData = response.data; 
                    this.promotions = response.data.data;
                    this.acumular_unidades();
                    this.set_search(false, true);
                    this.load = false;
                }).catch(error => {
                    this.load = false;
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                });
            },
            // BUSCAR PROMOCIÓN POR FOLIO
            porFolio(){
                axios.get('/buscar_folio_promo', {params: {folio: this.folio}}).then(response => {
                    if(response.data.id != undefined){
                        this.promotionsData = {};
                        this.promotions = [];
                        this.promotions.push(response.data);
                        this.acumular_unidades();
                    }
                    else{
                        this.makeToast('warning', 'El folio no existe');
                    }
                }).catch(error => {
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                });
            },
            // BUSCAR POR PLANTEL
            porPlantel(){
                if(this.queryPlantel !== null){
                    if(this.queryPlantel.length > 0){
                        this.http_plantel();
                    } else {
                        this.queryPlantel = null;
                    }
                }
            },
            http_plantel(page = 1){
                this.load = true;
                axios.get(`/buscar_plantel?page=${page}`, {params: {queryPlantel: this.queryPlantel}}).then(response => {
                    this.promotionsData = response.data; 
                    this.promotions = response.data.data;
                    this.inicio = '0000-00-00';
                    this.final = '0000-00-00';
                    this.acumular_unidades();
                    this.set_search(true, false);
                    this.load = false;
                }).catch(error => {
                    this.load = false;
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                });
            },
            set_search(searchPlantel, searchFecha){
                this.searchPlantel = searchPlantel;
                this.searchFecha = searchFecha;
            },
            // INICIALIZAR PARA CREAR UNA PROMOCIÓN
            registrarPromocion(){
                this.load = true;
                this.options = [];
                axios.get('/remisiones/get_responsables').then(response => {
                    this.options = this.assign_responsables(this.options, response.data);
                    this.listadoPromociones = false;
                    this.eliminarTemporal();
                    this.promocion = {
                        id: null,
                        cliente_id: null,
                        folio: '',
                        plantel: '',
                        descripcion: '',
                        unidades: 0,
                        created_at: '',
                        departures: [],
                        entregado_por: null,
                        estado: null
                    };
                    this.state = null;
                    this.registros = [];
                    this.mostrarRegistrar = true;
                    this.load = false;
                }).catch(error => {
                    this.load = false;
                });
            },
            // MOSTRAR DETALLES DE LA PROMOCIÓN
            detallesPromotion(promotion){
                this.promocion.departures = [];
                axios.get('/obtener_departures', {params: {promotion_id: promotion.id}}).then(response => {
                    this.promotion = response.data;
                    this.listadoPromociones = false;
                    this.mostrarDetalles = true;
                }).catch(error => {
                    this.load = false;
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                });
            },
            // MODAL PARA CONFIRMAR LA PROMOCIÓN
            confirmarPromocion(){
                if(this.queryCliente.length > 3){
                    this.state = true;
                    if(this.promocion.entregado_por !== null){
                        this.stateResp = true;
                        if(this.registros.length > 0){
                            this.$refs['modal-confirmar-promocion'].show();
                        } else {
                            this.makeToast('warning', 'Aun no se ha agregado un libro a la promoción.');
                        }
                    } else {
                        this.stateResp = false;
                        this.makeToast('warning', 'Elegir el responsable de la entrega.');
                    }
                }
                else{
                    this.state = false;
                    this.makeToast('warning', 'Campo obligatorio, elegir cliente.');
                }
            },
            // GUARDAR LA PROMOCIÓN
            guardarPromocion(){
                if(this.queryCliente.length > 3){
                    this.state = true;
                    if(this.promocion.entregado_por !== null){
                        this.stateResp = true;
                        this.load = true;
                        this.promocion.departures = this.registros;
                        axios.post('/guardar_promocion', this.promocion).then(response => {
                            this.load = false;
                            this.$refs['modal-confirmar-promocion'].hide();
                            this.messageAlert('center', 'success', 'La promoción se guardó correctamente.', null, 'reload');
                        })
                        .catch(error => {
                            this.load = false;
                            this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                        });
                    } else {
                        this.stateResp = false;
                        this.makeToast('warning', 'Elegir el responsable de la entrega.');
                    }
                    
                }
                else{
                    this.state = false;
                    this.makeToast('warning', 'Campo obligatorio, elegir cliente.');
                }
            },
            // ELIMINAR REGISTRO DEL ARRAY
            eliminarRegistro(i){
                this.registros.splice(i, 1);
                this.acum_unidades_crear();
            },
            // BUSCAR LIBRO POR ISBN
            buscarLibroISBN(){
                if(this.role_id == 6){
                    axios.get('/buscarISBN', {params: {isbn: this.temporal.ISBN}}).then(response => {
                        this.assignar_valores(response.data[0]);
                    }).catch(error => {
                        this.makeToast('danger', 'ISBN incorrecto');
                    });
                } else {
                    axios.get('/libro/by_editorial_type_isbn', {params: {
                        isbn: this.temporal.ISBN, editorial: 'MAJESTIC EDUCATION', typeNot: 'null'
                    }}).then(response => {
                        this.assignar_valores(response.data[0]);
                    }).catch(error => {
                        this.makeToast('danger', 'ISBN incorrecto');
                    });
                }
            },
            assignar_valores(libro){
                this.temporal.id = libro.id;
                this.temporal.type = libro.type;
                this.temporal.ISBN = libro.ISBN;
                this.temporal.titulo = libro.titulo;
                this.temporal.piezas = libro.piezas;
                this.inputISBN = false;
                this.inputLibro = false;
                this.inputUnidades = true;
            },
            // MOSTRAR COINCIDENCIA DE LIBROS
            mostrarLibros(){
                if(this.temporal.titulo.length > 0){
                    if(this.role_id == 6){
                        this.getLibros(this.temporal.titulo);
                    } else {
                        axios.get('/libro/by_editorial_type_titulo', {params: {titulo: this.temporal.titulo, editorial: 'MAJESTIC EDUCATION', typeNot: 'null'}}).then(response => {
                            this.resultslibros = response.data;
                        }).catch(error => {
                            // this.makeToast('danger', 'ISBN incorrecto');
                        });
                    }
                }
            },
            // SELECCIONAR LIBRO
            datosLibro(libro){
                this.temporal = {
                    id: libro.id,
                    type: libro.type,
                    ISBN: libro.ISBN,
                    titulo: libro.titulo,
                    tipo: null,
                    unidades: null,
                    piezas: libro.piezas,
                    codigos: 0
                };
                this.resultslibros = [];
                this.inputISBN = false;
                this.inputLibro = false;
                this.inputUnidades = true;
            },
            validar_insert(condicion, existencia){
                if(condicion){
                    var pzs = existencia;
                    if(this.registros.length > 0){
                        var acum = 0;
                        this.registros.forEach(registro => {
                            if(this.temporal.id == registro.id && this.temporal.tipo == registro.tipo) {
                                acum += parseInt(registro.unidades);
                                pzs = existencia - acum;
                            }
                        });
                    }
                    if(this.temporal.unidades <= pzs){
                        this.registros.push(this.temporal);
                        this.acum_unidades_crear();
                        this.eliminarTemporal();
                    }else{
                        this.makeToast('warning', `${pzs} unidades en existencia`);
                    }
                }
            },
            // VERIFICAR UNIDADES
            guardarRegistro() {
                var check = this.registros.find(d => d.id == this.temporal.id);
                if(check == undefined){
                    if(this.temporal.unidades > 0){
                        if (this.role_id != 6) {
                            if (this.temporal.unidades < 31 || (this.temporal.titulo.includes('CATALOGO') == true && this.temporal.unidades < 251)) {
                                if (this.temporal.type != 'digital') {
                                    axios.get('/libro/get_scratch', { params: { id: this.temporal.id } }).then(response => {
                                        this.validar_insert(this.temporal.type != 'digital', this.temporal.piezas - response.data);
                                    }).catch(error => { });
                                }
                                this.validar_insert(this.temporal.type == 'digital', this.temporal.codigos);
                            } else {
                                this.makeToast('warning', 'Las unidades no pueden ser mayor a 30 o mayor a 250 en catálogo.');
                            }
                        } else {
                            if (this.temporal.type != 'digital') {
                                axios.get('/libro/get_scratch', { params: { id: this.temporal.id } }).then(response => {
                                    this.validar_insert(this.temporal.type != 'digital', this.temporal.piezas - response.data);
                                }).catch(error => { });
                            }
                            this.validar_insert(this.temporal.type == 'digital', this.temporal.codigos);
                        }
                    } else{
                        this.makeToast('warning', 'Unidades invalidas');
                    }
                } else{
                    this.makeToast('warning', 'El libro ya ha sido agregado.');
                }
            },
            // ELIMINAR REGISTRO TEMPORAL
            eliminarTemporal(){
                this.temporal = {
                    id: 0,
                    type: null,
                    ISBN: '',
                    titulo: '',
                    tipo: null,
                    unidades: null,
                    piezas: 0,
                    codigos: 0
                };
                this.inputUnidades = false;
                this.inputLibro = true;
                this.inputISBN = true;
            },
            acumular_unidades(){
                this.total_unidades = 0;
                this.promotions.forEach(promotion => {
                    this.total_unidades += promotion.unidades_pendientes;
                });
            },
            acum_unidades_crear(){
                this.unidades_crear = 0;
                this.registros.forEach(registro => {
                    this.unidades_crear += parseInt(registro.unidades);
                });
            },
            makeToast(variant = null, descripcion) {
                this.$bvToast.toast(descripcion, {
                    title: 'Mensaje',
                    variant: variant,
                    solid: true
                })
            },
            cancel_promotion(){
                this.load = true;
                let form = {promotion_id: this.promotion.id};
                axios.put('/promotions/cancel', form).then(response => {
                    this.load = false;
                    this.messageAlert('center', 'success', 'La promoción se ha cancelado', null, 'reload');
                }).catch(error => {
                    this.load = false;
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                });
            },
            rowClass(item, type) {
                if (!item) return
                if (item.estado == 'Cancelado') return 'table-danger';
                if (item.estado == 'Enviado') return;
            },
            // REGISTRAR DEVOLUCION
            registrarDevolucion(promotion){
                this.load = true;
                axios.get('/obtener_departures', {params: {promotion_id: promotion.id}}).then(response => {
                    this.formDev.id = response.data.id;
                    this.formDev.folio = response.data.folio;
                    this.formDev.unidades_devolucion = 0;
                    this.formDev.departures = [];
                    response.data.departures.forEach(departure => {
                        let datos = {
                            departure_id: departure.id,
                            libro_id: departure.libro_id,
                            type: departure.libro.type,
                            ISBN: departure.libro.ISBN,
                            titulo: departure.libro.titulo,
                            unidades_pendientes: departure.unidades_pendientes,
                            unidades: 0
                        };
                        this.formDev.departures.push(datos);
                    });
                    this.listadoPromociones = false;
                    this.mostrarDevolucion = true;
                    this.load = false;
                }).catch(error => {
                    this.load = false;
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                });
            },
            // REVISAR QUE LAS UNIDADES INGRESADAS SEAN MENOR O IGUAL A LAS PENDIENTES
            checkUnidades(departure, i){
                let unidades = parseInt(departure.unidades);
                if(unidades >= 0){
                    if(unidades <= departure.unidades_pendientes){
                        // if(i + 1 < this.formDev.departures.length){
                        //     document.getElementById('inpDev-'+(i+1)).focus();
                        //     document.getElementById('inpDev-'+(i+1)).select();
                        // }
                    } else {
                        departure.unidades = 0;
                        this.makeToast('warning', 'El numero de unidades que ingresaste es mayor a las unidades pendientes.');
                    }
                } else {
                    departure.unidades = 0;
                    this.makeToast('warning', 'El numero de unidades no puede ser menor a 0.');
                }
                this.acumular_devolucion();
            },
            // SUMAR LAS UNIDADES DE LA DEVOLUCION
            acumular_devolucion(){
                this.formDev.unidades_devolucion = 0;
                this.formDev.departures.forEach(departure => {
                    this.formDev.unidades_devolucion += parseInt(departure.unidades);
                });
            },
            // GUARDAR LA DEVOLUCION
            guardarDevolucion(){
                this.load = true;
                axios.post('/promotions/devolucion', this.formDev).then(response => {
                    this.load = false;
                    this.messageAlert('center', 'success', 'La devolución se guardó correctamente.', null, 'reload');
                }).catch(error => {
                    this.load = false;
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                });
            },
            selectCliente(cliente){
                this.queryCliente = cliente.name;
                this.promocion.cliente_id = cliente.id;
                this.promocion.plantel = cliente.name;
                this.clientes = [];
            },
            checkDisponible(){
                this.load = true;
                axios.get('/codes/by_libro_count', {params: {libro_id: this.temporal.id, tipo: this.temporal.tipo, estado: 'inventario'}}).then(response => {
                    this.temporal.codigos = response.data;
                    this.temporal.unidades = 0;
                    this.load = false;
                }).catch(error => {
                    this.load = false;
                });
            },
            // SELECCIONAR/GUARDAR PAQUETERIA DE PROMOCIÓN
            selectPaqueteria(promotion, i) {
                this.position = i;
                this.promotion_id = promotion.id;
                this.$refs['modalPaqueteria'].show();
            },
            // RESULTADO DE GUARDAR PAQUETERIA
            savedEnvio(promotion){
                this.$refs['modalPaqueteria'].hide();
                this.messageAlert('center', 'success', 'Los datos se guardaron correctamente.', null, 'reload');
            },
        }
    }
</script>

<style>
    #txtObligatorio {
        color: red;
    }
    #listaBL{
        position: absolute;
        z-index: 100
    }
</style>