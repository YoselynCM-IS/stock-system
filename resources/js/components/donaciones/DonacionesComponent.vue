<template>
    <div>
        <check-connection-component></check-connection-component>
        <div v-if="listadoDonaciones">
            <b-row>
                <!-- BUSCAR DONACIÓN POR PLANTEL -->
                <b-col sm="6">
                    <b-row class="my-1">
                        <b-col sm="2">
                            <label for="input-plantel">Plantel</label>
                        </b-col>
                        <b-col sm="10">
                            <b-input style="text-transform:uppercase;" v-model="queryPlantel" @keyup="porPlantel()"></b-input>
                        </b-col>
                    </b-row>
                </b-col> 
                <!-- CREAR UNA DONACIÓN -->
                <b-col sm="6">
                    <b-row>
                        <b-col sm="3">
                            <label for="input-inicio">De:</label>
                        </b-col>
                        <b-col sm="9">
                            <input 
                                class="form-control" 
                                type="date" 
                                :state="stateDate"
                                v-model="inicio"
                                @change="porFecha()">
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col sm="3">
                            <label for="input-final">A: </label>
                        </b-col>
                        <b-col sm="9">
                            <input 
                                class="form-control" 
                                type="date" 
                                v-model="final"
                                @change="porFecha()">
                        </b-col>
                    </b-row>
                </b-col> 
            </b-row>
            <br>
            <b-row>
                <b-col>
                    <!-- PAGINACIÓN -->
                    <pagination size="default" :limit="1" :data="regalosData" 
                        @pagination-change-page="getResults">
                        <span slot="prev-nav"><i class="fa fa-angle-left"></i></span>
                        <span slot="next-nav"><i class="fa fa-angle-right"></i></span>
                    </pagination>
                </b-col>
                <b-col class="text-right">
                    <a 
                        v-if="regalos.length > 0"
                        class="btn btn-dark"
                        :href="'/download_donacion/' + queryPlantel + '/' + inicio + '/' + final + '/general'">
                        <i class="fa fa-download"></i> General
                    </a>
                    <a 
                        v-if="regalos.length > 0 && role_id !== 3"
                        class="btn btn-dark"
                        :href="'/download_donacion/' + queryPlantel + '/' + inicio + '/' + final + '/detallado'">
                        <i class="fa fa-download"></i> Detallado
                    </a>
                </b-col>
                <b-col sm="3" class="text-right">
                    <b-button v-if="role_id === 1 || role_id == 2 || role_id == 6" variant="success" @click="registrarDonacion()">
                        <i class="fa fa-plus"></i> Registrar donación
                    </b-button>
                </b-col>
            </b-row>
            <div v-if="!load">
                <!-- LISTADO DE DONACIONES -->
                <b-table v-if="regalos.length > 0" responsive
                    hover :tbody-tr-class="rowClass"
                    :items="regalos" :fields="fields">
                    <template v-slot:cell(index)="row">{{ row.index + 1 }}</template>
                    <template v-slot:cell(created_at)="row">{{ row.item.created_at | moment }}</template>
                    <template v-slot:cell(detalles)="row">
                        <b-button variant="info" @click="detallesDonacion(row.item)">Detalles</b-button>
                    </template>
                    <template v-slot:cell(entregado_por)="row">
                        <b-button 
                            variant="warning" 
                            v-if="row.item.entregado_por === null && (role_id === 1 || role_id === 2 || role_id === 3 || role_id == 6)"
                            :disabled="load"
                            v-on:click="marcarEntrega(row.item, row.index)">
                            <i class="fa fa-frown-o"></i>
                        </b-button>
                    </template>
                    <template #thead-top="row">
                        <tr>
                            <th colspan="2"></th>
                            <th>{{ total_unidades | formatNumber }}</th>
                            <th colspan="2"></th>
                        </tr>
                    </template>
                </b-table>
                <div v-else>
                    <br><b-alert show variant="dark"><i class="fa fa-warning"></i> No se encontraron registros</b-alert>
                </div>
                <!-- MODALS -->
                <!-- ELEGIR RESPONSABLE DE LA ENTREGA -->
                <b-modal v-model="marcarDon" title="Responsable de la entrega">
                    <b-row>
                        <b-col sm="8">
                            <b-form-select :state="stateResp" v-model="regalo.entregado_por" :options="options"></b-form-select>
                        </b-col>
                        <b-col sm="4">
                            <b-button v-on:click="guardarEntrega()" :disabled="load" variant="success">
                                <i class="fa fa-check"></i> Guardar <b-spinner v-if="load" small></b-spinner>
                            </b-button>
                        </b-col>
                    </b-row>
                    <template v-slot:modal-footer>
                        <b-alert show variant="info">
                            <i class="fa fa-exclamation-circle"></i> Verificar los datos antes de presionar <b>Guardar</b>, ya que después no se podrán realizar cambios.
                        </b-alert>
                    </template>
                </b-modal>
            </div>
            <load-component v-else></load-component>
        </div>
        <!-- MOSTRAR DETALLES DE LA DONACION -->
        <div v-if="mostrarDetalles">
            <b-row>
                <b-col>
                    <h6><b>Plantel</b>: {{ regalo.plantel }}</h6>
                    <h6><b>Fecha</b>: {{ regalo.created_at | moment }}</h6>
                </b-col>
                <b-col>
                    <h6 v-if="regalo.entregado_por != null"><b>Entregado por</b>: {{ regalo.entregado_por }}</h6>
                    <h6 v-if="regalo.creado_por != null"><b>Creado por</b>: {{ regalo.creado_por }}</h6>
                </b-col>
                <b-col sm="2">
                    <b-button variant="dark" :href="`/download_regalo/${regalo.id}`"><i class="fa fa-download"></i> Descargar</b-button>
                </b-col>
                <b-col sm="2" align="right">
                    <b-button variant="secondary" @click="listadoDonaciones = true; mostrarDetalles = false;">
                        <i class="fa fa-mail-reply"></i> Regresar
                    </b-button>
                </b-col>
            </b-row>
            <h6 v-if="regalo.descripcion != null"><b>Descripción</b>: {{ regalo.descripcion }}</h6>
            <b-table :items="regalo.donaciones" :fields="fieldsD">
                <template v-slot:cell(index)="row">{{ row.index + 1 }}</template>
                <template v-slot:cell(ISBN)="row">{{ row.item.libro.ISBN }}</template>
                <template v-slot:cell(titulo)="row">{{ row.item.libro.titulo }}</template>
                <template #cell(codes)="row">
                    <b-button v-if="row.item.codes.length > 0" 
                        size="sm" @click="row.toggleDetails" pill variant="info">
                        {{ row.detailsShowing ? 'Ocultar' : 'Mostrar' }}
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
                <template #thead-top="row">
                    <tr>
                        <th colspan="3"></th>
                        <th>{{ regalo.unidades }}</th>
                    </tr>
                </template>
            </b-table>
        </div>
        <!-- CREAR UNA DONACION -->
        <div v-if="mostrarRegistrar">
            <b-row>
                <b-col sm="6"><h4 style="color: #170057">Registrar donación</h4></b-col>
                <b-col sm="3" align="right">
                    <b-button variant="success" @click="confirmarDonacion()" :disabled="load">
                        <i class="fa fa-check"></i> {{ !load ? 'Guardar' : 'Guardando' }} <b-spinner small v-if="load"></b-spinner>
                    </b-button>
                </b-col>
                <b-col sm="3" align="right">
                    <b-button variant="secondary" @click="listadoDonaciones = true; mostrarRegistrar = false;">
                        <i class="fa fa-mail-reply"></i> Regresar
                    </b-button>
                </b-col>
            </b-row>
            <hr>
            <b-row class="col-md-10">
                <b-col sm="2"><label><b>Cliente</b>: <b id="txtObligatorio">*</b></label></b-col>
                <b-col>
                    <b-input v-model="queryCliente" @keyup="mostrarClientes()" autofocus
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
            <b-row class="col-md-10">
                <b-col sm="3"><label><b>Descripción (Opcional)</b>:</label></b-col>
                <b-col>
                    <b-input style="text-transform:uppercase;" type="text" v-model="regalo.descripcion"></b-input>
                </b-col>
            </b-row>
            <hr>
            <b-table :items="regalo.donaciones" :fields="fieldsR">
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
                            <th>Unidades</th>
                        </tr>
                        <tr>
                            <th colspan="1"></th>
                            <th>
                                <b-input
                                    id="input-isbn"
                                    autofocus
                                    v-model="temporal.ISBN"
                                    @keyup.enter="buscarLibroISBN()"
                                    v-if="inputISBN"
                                    :disabled="load"
                                ></b-input>
                                <label v-if="!inputISBN">{{ temporal.ISBN }}</label>
                            </th>
                            <th>
                                <b-input
                                    style="text-transform:uppercase;"
                                    id="input-libro"
                                    autofocus
                                    v-model="temporal.titulo"
                                    @keyup="mostrarLibros()"
                                    v-if="inputLibro"
                                    :disabled="load">
                                </b-input>
                                <div class="list-group" v-if="resultslibros.length" id="listaBL">
                                    <a 
                                        href="#" 
                                        v-bind:key="i" 
                                        class="list-group-item list-group-item-action" 
                                        v-for="(libro, i) in resultslibros" 
                                        @click="datosLibro(libro)">
                                        {{ libro.titulo }}
                                    </a>
                                </div>
                                <label v-if="!inputLibro">{{ temporal.titulo }}</label>
                            </th>
                            <th>
                                <b-form-input 
                                    id="input-unidades"
                                    autofocus
                                    @keyup.enter="guardarRegistro()"
                                    v-if="inputUnidades"
                                    v-model="temporal.unidades" 
                                    type="number"
                                    required
                                    :disabled="load">
                                </b-form-input>
                            </th>
                            <th>
                                <b-button 
                                    variant="secondary"
                                    @click="eliminarTemporal()" 
                                    v-if="inputUnidades"
                                    :disabled="load">
                                    <i class="fa fa-minus-circle"></i>
                                </b-button>
                            </th>
                        </tr>
                        <tr>
                            <th colspan="3"></th>
                            <th>{{ regalo.unidades | formatNumber }}</th>
                            <th></th>
                        </tr>
                </template>
            </b-table>
            <!-- RESUMEN DE LA DONACION -->
            <b-modal ref="modal-confirmar-regalo" size="xl" title="Resumen de la donación">
                <label>
                    <b>Plantel: </b><label style="text-transform:uppercase;">{{ regalo.plantel }}</label>
                </label><br>
                <label v-if="regalo.descripcion != ''">
                    <b>Descripción: </b><label style="text-transform:uppercase;">{{ regalo.descripcion }}</label>
                </label>
                <b-table :items="regalo.donaciones" :fields="fieldsD">
                    <template v-slot:cell(index)="row">{{ row.index + 1 }}</template>
                    <template v-slot:cell(ISBN)="row">{{ row.item.ISBN }}</template>
                    <template v-slot:cell(titulo)="row">{{ row.item.titulo }}</template>
                    <template #thead-top="row">
                        <tr>
                            <th colspan="3"></th>
                            <th>{{ regalo.unidades | formatNumber }}</th>
                        </tr>
                    </template>
                </b-table>
                <div slot="modal-footer">
                    <b-row>
                        <b-col sm="10">
                            <b-alert show variant="info">
                                <i class="fa fa-exclamation-circle"></i>
                                <b>Verificar los datos de la donación.</b> En caso de algún error, modificar antes de presionar <b>Confirmar</b> ya que después no se podrán realizar cambios.
                            </b-alert>
                        </b-col>
                        <b-col sm="2" align="right">
                             <b-button variant="success" @click="guardarDonacion()" :disabled="load">
                                <i class="fa fa-check"></i> Confirmar
                            </b-button>
                        </b-col>
                    </b-row>
                </div>
            </b-modal>
        </div>
    </div>
</template>

<script>
import setResponsables from '../../mixins/setResponsables'
import getLibros from '../../mixins/getLibros';
import searchCliente from '../../mixins/searchCliente';
    export default {
        props: ['role_id'],
        mixins: [setResponsables,getLibros,searchCliente],
        data() {
            return {
                regalosData: {},
                listadoDonaciones: true,
                mostrarRegistrar: false,
                regalos: [],
                fields: [
                    {key: 'index', label: 'N.'},
                    'plantel', 
                    'unidades',
                    {key: 'created_at', label: 'Fecha de creación'},
                    {key: 'detalles', label: ''},
                    {key: 'entregado_por', label: ''}
                ],
                load: false,
                fieldsR: [
                    {key: 'index', label: 'N.'}, 
                    {key: 'ISBN', label: 'ISBN'}, 
                    {key: 'titulo', label: 'Libro'}, 
                    'unidades', 'eliminar'
                ],
                fieldsD: [
                    {key: 'index', label: 'N.'}, 
                    {key: 'ISBN', label: 'ISBN'}, 
                    {key: 'titulo', label: 'Libro'}, 
                    'unidades',
                    { key: 'codes', label: '' },
                ],
                temporal: {
                    id: 0,
                    ISBN: '',
                    titulo: '',
                    unidades: null,
                    piezas: 0
                },
                regalo: {
                    id: null,
                    cliente_id: null,
                    plantel: '',
                    descripcion: '',
                    unidades: 0,
                    created_at: '',
                    donaciones: [],
                    entregado_por: null,
                    creado_por: null
                },
                inputISBN: true,
                inputLibro: true,
                inputUnidades: false,
                state: null,
                mostrarDetalles: false,
                queryPlantel: null,
                loadRegisters: false,
                stateDate: null,
                inicio: '0000-00-00',
                final: '0000-00-00',
                total_unidades: 0,
                options: [],
                stateResp: null,
                position: null,
                marcarDon: false,
                searchPlantel: false,
                searchFecha: false,
                fieldsCodes: [
                    { key: 'index', label: 'N.' },
                    { key: 'codigo', label: 'Código' }
                ],
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
        created: function(){
            this.getResults();
        },
        methods: {
            // OBTENER TODAS LAS DONACIONES
            getResults(page = 1){
                if(!this.searchPlantel && !this.searchFecha)
                    this.http_regalos(page);
                if(this.searchPlantel)
                    this.http_plantel(page);
                if(this.searchFecha)
                    this.http_fecha(page);
            },
            // HTTP REGALOS
            http_regalos(page = 1){
                this.load = true;
                axios.get(`/donaciones/index?page=${page}`).then(response => {
                    this.regalosData = response.data;
                    this.regalos = response.data.data;
                    this.acumular_unidades();
                    this.set_search(false, false);
                    this.load = false;
                }).catch(error => {
                    this.load = true;
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                });
            },
            rowClass(item, type) {
                if (!item) return
                if (item.entregado_por !== null) return 'table-success'
            },
            // INICIALIZAR PARA MARCAR LA ENTREGA DE LA DONACIÓN
            marcarEntrega(regalo, i){
                this.load = true;
                this.options = [];
                axios.get('/remisiones/get_responsables').then(response => {
                    this.options = this.assign_responsables(this.options, response.data);
                    this.regalo.id = regalo.id;
                    this.regalo.entregado_por = null;
                    this.position = i;
                    this.stateResp = null;
                    this.marcarDon = true;
                    this.load = false;
                }).catch(error => {
                    this.load = false;
                });
            },
            // GUARDAR EL RESPONSABLE DE LA ENTREGA
            guardarEntrega(){
                if(this.regalo.entregado_por != null){
                    this.load = true;
                    this.stateResp = true;
                    axios.put('/entrega_donacion', this.regalo).then(response => {
                        this.load = false;
                        this.regalos[this.position].entregado_por = response.data.entregado_por;
                        this.marcarDon = false;
                        this.makeToast('success', 'La donación ha sido marcada como entregada');
                    }).catch(error => {
                        this.load = false;
                        this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                    });
                } else {
                    this.stateResp = false;
                    this.makeToast('warning', 'Seleccionar responsable para poder continuar.');
                }
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
                axios.get(`/donaciones/by_fecha?page=${page}`, {
                    params: {inicio: this.inicio, final: this.final, plantel: this.queryPlantel}}).then(response => {
                    this.regalosData = response.data;
                    this.regalos = response.data.data;
                    this.load = false;
                    this.set_search(false, true);
                    this.acumular_unidades();
                }).catch(error => {
                    this.load = false;
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                });
            },
            set_search(searchPlantel, searchFecha){
                this.searchPlantel = searchPlantel;
                this.searchFecha = searchFecha;
            },
            // BUSCAR POR PLANTEL
            porPlantel(){
                if(this.queryPlantel !== null){
                    if(this.queryPlantel.length > 0){
                        this.http_plantel();
                    } else{
                        this.queryPlantel = null;
                    }
                }
            },
            http_plantel(page = 1){
                this.load = true;
                axios.get(`/donaciones/by_plantel?page=${page}`, {params: {queryPlantel: this.queryPlantel}}).then(response => {
                    this.regalosData = response.data;
                    this.regalos = response.data.data;
                    this.inicio = '0000-00-00';
                    this.final = '0000-00-00';
                    this.acumular_unidades();
                    this.load = false;
                    this.set_search(true, false);
                }).catch(error => {
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                    this.load = false;
                });
            },
            // INICIALIZAR PARA CREAR UNA DONACION
            registrarDonacion(){
                this.listadoDonaciones = false;
                this.eliminarTemporal();
                this.regalo = {
                    id: null,
                    cliente_id: null,
                    plantel: '',
                    descripcion: '',
                    unidades: 0,
                    created_at: '',
                    donaciones: [],
                    entregado_por: null
                };
                this.state = null;
                this.mostrarRegistrar = true;
            },
            // MOSTRAR DETALLES DE LA DONACION
            detallesDonacion(regalo){
                axios.get('/detalles_donacion', {params: {regalo_id: regalo.id}}).then(response => {
                    this.regalo.id = response.data.id;
                    this.regalo.donaciones = response.data.donaciones;
                    this.regalo.plantel = response.data.plantel;
                    this.regalo.unidades = response.data.unidades;
                    this.regalo.descripcion = response.data.descripcion;
                    this.regalo.created_at = response.data.created_at;
                    this.regalo.entregado_por = response.data.entregado_por;
                    this.regalo.creado_por = response.data.creado_por;
                    this.listadoDonaciones = false;
                    this.mostrarDetalles = true;
                }).catch(error => {
                    this.load = false;
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                });
            },
            // MODAL PARA CONFIRMAR LA DONACION
            confirmarDonacion(){
                if(this.queryCliente.length > 4){
                    this.state = true;
                    if(this.regalo.donaciones.length > 0){
                        this.$refs['modal-confirmar-regalo'].show();
                    } else {
                        this.makeToast('warning', 'Aun no se ha agregado un libro a la donación.');
                    }
                }
                else{
                    this.state = false;
                    this.makeToast('warning', 'Campo obligatorio, elegir cliente.');
                }
            },
            // GUARDAR LA DONACION
            guardarDonacion(){
                if(this.queryCliente.length > 4){
                    this.state = true;
                    this.load = true;
                    axios.post('/donaciones/store', this.regalo).then(response => {
                        this.load = false;
                        this.regalos.unshift(response.data);
                        this.acumular_unidades();
                        this.makeToast('success', 'La donación se guardo correctamente.');
                        this.$refs['modal-confirmar-regalo'].hide();
                        this.mostrarRegistrar = false;
                        this.listadoDonaciones = true;
                    })
                    .catch(error => {
                        this.load = false;
                        this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                    });
                }
                else{
                    this.state = false;
                    this.makeToast('warning', 'Campo obligatorio, elegir cliente.');
                }
            },
            // ELIMINAR REGISTRO DEL ARRAY
            eliminarRegistro(i){
                this.regalo.donaciones.splice(i, 1);
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
                    axios.get('/libro/by_editorial_type_isbn', {params: {isbn: this.temporal.ISBN, editorial: 'MAJESTIC EDUCATION', typeNot: 'digital'}}).then(response => {
                        this.assignar_valores(response.data[0]);
                    }).catch(error => {
                        this.makeToast('danger', 'ISBN incorrecto');
                    });
                }
            },
            assignar_valores(libro){
                this.temporal.id = libro.id;
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
                        axios.get('/libro/by_editorial_type_titulo', {params: {titulo: this.temporal.titulo, editorial: 'MAJESTIC EDUCATION', typeNot: 'digital'}}).then(response => {
                            this.resultslibros = response.data;
                        }).catch(error => {
                            this.makeToast('danger', 'ISBN incorrecto');
                        });
                    }
                }
            },
            // SELECCIONAR LIBRO
            datosLibro(libro){
                this.temporal = {
                    id: libro.id,
                    ISBN: libro.ISBN,
                    titulo: libro.titulo,
                    unidades: null,
                    piezas: libro.piezas
                };
                this.resultslibros = [];
                this.inputISBN = false;
                this.inputLibro = false;
                this.inputUnidades = true;
            },
            // VERIFICAR UNIDADES
            guardarRegistro(){
                var check = this.regalo.donaciones.find(d => d.id == this.temporal.id);
                if(check == undefined){
                    if (this.temporal.unidades > 0) {
                        axios.get('/libro/get_scratch', { params: { id: this.temporal.id } }).then(response => {
                            this.params_donation(this.temporal.piezas - response.data);
                        }).catch(error => { });
                    }
                    else{
                        this.makeToast('warning', 'Unidades invalidas');
                    }
                } else{
                    this.makeToast('warning', 'El libro ya ha sido agregado.');
                }
            },
            params_donation(pzs) {
                if (this.temporal.unidades <= pzs) {
                    this.regalo.donaciones.push(this.temporal);
                    this.acum_unidades_crear();
                    this.eliminarTemporal();
                }
                else {
                    this.makeToast('warning', `${pzs} unidades en existencia`);
                }  
            },
            // ELIMINAR REGISTRO TEMPORAL
            eliminarTemporal(){
                this.temporal = {
                    id: 0,
                    ISBN: '',
                    titulo: '',
                    unidades: null,
                    piezas: 0
                };
                this.inputUnidades = false;
                this.inputLibro = true;
                this.inputISBN = true;
            },
            acumular_unidades(){
                this.total_unidades = 0;
                this.regalos.forEach(regalo => {
                    this.total_unidades += regalo.unidades;
                });
            },
            acum_unidades_crear(){
                this.regalo.unidades = 0;
                this.regalo.donaciones.forEach(registro => {
                    this.regalo.unidades += parseInt(registro.unidades);
                });
            },
            makeToast(variant = null, descripcion) {
                this.$bvToast.toast(descripcion, {
                    title: 'Mensaje',
                    variant: variant,
                    solid: true
                })
            },
            selectCliente(cliente){
                this.queryCliente = cliente.name;
                this.regalo.cliente_id = cliente.id;
                this.regalo.plantel = cliente.name;
                this.clientes = [];
            }
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