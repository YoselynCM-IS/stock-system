<template>
    <div>
        <check-connection-component></check-connection-component>
        <div v-if="listaRemisiones">
            <div class="row">
                <!-- BUSCAR REMISION POR NUMERO -->
                <div class="col-md-3">
                    <b-row class="my-1">
                        <b-col class="text-right" sm="5">
                            <label for="input-numero">Folio</label>
                        </b-col>
                        <b-col sm="7">
                            <b-form-input 
                                id="input-numero" type="number" 
                                v-model="num_remision" 
                                @keyup.enter="porNumero()">
                            </b-form-input>
                        </b-col>
                    </b-row>
                </div>
                <div class="col-md-5">
                    <!-- BUSCAR POR CLIENTE -->
                    <b-row class="my-1">
                        <b-col class="text-right" sm="3">
                            <label for="input-cliente">Cliente</label>
                        </b-col>
                        <b-col sm="9">
                            <b-input style="text-transform:uppercase;"
                                v-model="queryCliente" @keyup="mostrarClientes()">
                            </b-input>
                            <div class="list-group" v-if="resultsClientes.length" id="listaL">
                                <a 
                                    href="#" 
                                    v-bind:key="i" 
                                    class="list-group-item list-group-item-action" 
                                    v-for="(result, i) in resultsClientes" 
                                    @click="porCliente(result)">
                                    {{ result.name }}
                                </a>
                            </div>
                        </b-col>
                    </b-row>
                    <hr>
                    <!-- BUSCAR REMISION POR ESTADO -->
                    <b-row class="my-1">
                        <b-col class="text-right" sm="3"><label for="input-estado">Estado</label></b-col>
                        <b-col sm="9">
                            <b-form-select v-model="estadoRemision" :options="estados" @change="porEstado()"></b-form-select>
                        </b-col>
                    </b-row>
                </div>
                <!-- BUSCAR POR FECHAS -->
                <div class="col-md-4">
                    <b-row>
                        <b-col class="text-right" sm="3">
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
                        <b-col class="text-right" sm="3">
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
                </div>
            </div>
            <hr>
            <!-- IMPIRMIR REPORTE -->
            <div>
                <b-row>
                    <b-col>
                        <!-- PAGINACIÓN -->
                        <pagination size="default" :limit="1" :data="remisionesData" 
                            @pagination-change-page="getResults">
                            <span slot="prev-nav"><i class="fa fa-angle-left"></i></span>
                            <span slot="next-nav"><i class="fa fa-angle-right"></i></span>
                        </pagination>
                    </b-col>
                    <b-col align="right">
                        <!-- <a 
                            v-if="remisiones.length > 0 && num_remision === null"
                            class="btn btn-dark"
                            :href="'/down_remisiones_pdf/' + cliente_id + '/' + inicio + '/' + final + '/' + estadoRemision">
                            <i class="fa fa-download"></i> PDF
                        </a> -->
                        <b-button 
                            v-if="remisiones.length > 0 && num_remision === null"
                            class="btn btn-dark" :disabled="cliente_id == null"
                            :href="'/down_gral_excel/' + cliente_id + '/' + inicio + '/' + final + '/' + estadoRemision">
                            <i class="fa fa-download"></i> General
                        </b-button>
                        <b-button
                            v-if="remisiones.length > 0 && num_remision === null"
                            class="btn btn-dark" :disabled="cliente_id == null"
                            :href="'/down_remisiones_excel/' + cliente_id + '/' + inicio + '/' + final + '/' + estadoRemision">
                            <i class="fa fa-download"></i> Detallado
                        </b-button>
                    </b-col>
                    <b-col sm="3" class="text-right">
                        <b-button v-if="role_id === 1 || role_id === 2 || role_id == 6" 
                            variant="success" :disabled="load" target="blank" 
                            :href="`/remisiones/ce_remision/${0}/${false}`">
                            <i class="fa fa-plus"></i> Crear remisión
                        </b-button>
                    </b-col>
                </b-row>
            </div>
            <!-- LISTADO DE REMISIONES -->
            <div>
                <div v-if="!load">
                    <!-- TABLA DE REMISIONES -->
                    <b-table v-if="remisiones.length" :busy="load"
                        responsive hover :items="remisiones" :fields="fields" 
                        :tbody-tr-class="rowClass">
                        <template v-slot:cell(cliente_id)="row">
                            {{ row.item.cliente.name }}
                        </template>
                        <template v-slot:cell(total)="row">
                            ${{ row.item.total | formatNumber }}
                        </template>
                        <template v-slot:cell(total_devolucion)="row">
                            ${{ row.item.total_devolucion | formatNumber }}
                        </template>
                        <template v-slot:cell(pagos)="row">
                            ${{ row.item.pagos | formatNumber }}
                        </template>
                        <template v-slot:cell(total_pagar)="row">
                            ${{ row.item.total_pagar | formatNumber }}
                        </template>
                        <template v-slot:cell(detalles)="row">
                            <b-button :href="`/remisiones/details/${row.item.id}`" 
                                target="blank" variant="info">
                                Detalles
                            </b-button>
                        </template>
                        <template v-slot:cell(responsable)="row">
                            <div>
                                <b-button @click="selectResponsable(row.item, row.index)"
                                    v-if="(role_id === 1 || role_id == 6 || role_id === 3) && row.item.responsable === null && row.item.estado !== 'Cancelado'"
                                    variant="dark" block><i class="fa fa-male"></i>
                                </b-button>
                                <b-button @click="selectPaqueteria(row.item, row.index)"
                                    v-if="(role_id === 1 || role_id == 6 || role_id === 2) && row.item.paqueteria_id == null && row.item.estado !== 'Cancelado'"
                                    variant="dark" block><i class="fa fa-truck"></i>
                                </b-button>
                            </div>
                        </template>
                        <template v-slot:cell(editar)="row">
                            <!-- <b-button v-if="(role_id == 6) && row.item.updated_at === row.item.created_at && row.item.total_pagar === row.item.total && row.item.estado !== 'Cancelado' && row.item.envio == false"
                                variant="warning" style="color: white;" target="blank"
                                :href="`/remisiones/ce_remision/${row.item.id}/${true}`">
                                <i class="fa fa-edit"></i>
                            </b-button> -->
                            <!-- row.item.updated_at === row.item.created_at && row.item.total_pagar === row.item.total &&  -->
                            <b-button v-if="(role_id == 6) && row.item.estado !== 'Cancelado'"
                                variant="warning" style="color: white;" target="blank"
                                :href="`/remisiones/ce_remision/${row.item.id}/${true}`">
                                <i class="fa fa-edit"></i>
                            </b-button>
                        </template>
                        <template #thead-top="row">
                            <tr v-if="total_salida > 0 && !loadTotales">
                                <th colspan="3"></th>
                                <th>${{ total_salida | formatNumber }}</th>
                                <th>${{ total_pagos | formatNumber }}</th>
                                <th>${{ total_devolucion | formatNumber }}</th>
                                <th>${{ total_pagar | formatNumber }}</th>
                                <th colspan="3"></th>
                            </tr>
                            <tr v-if="loadTotales">
                                <th colspan="10" class="text-center text-dark my-2 mt-3">
                                    <b-spinner small class="align-middle" variant="dark"></b-spinner>
                                    <strong>Cargando totales...</strong>
                                </th>
                            </tr>
                        </template>
                    </b-table>
                    <b-alert v-else show variant="secondary">
                        <i class="fa fa-warning"></i> No se encontraron registros.
                    </b-alert>
                </div>
                <div v-else class="text-center text-info my-2 mt-3">
                    <b-spinner class="align-middle"></b-spinner>
                    <strong>Cargando...</strong>
                </div>
                <!-- <label style="font-size: 12px;">En el total de <b>Salida</b> no se incluyen las remisiones canceladas.</label> -->
            </div>
            <!-- MODALS -->
            <!-- ELEGIR RESPONSABLE DE LA ENTREGA -->
            <b-modal ref="modalMarcarE" id="modal-marcar" title="Responsable de la entrega" hide-footer>
                <b-form @submit.prevent="saveResponsable()">
                    <b-row>
                        <b-col>
                            <b-form-group label="Responsable de la entrega">
                                <b-form-select :disabled="load" v-model="form.responsable" :options="options" required></b-form-select>
                            </b-form-group>
                        </b-col>
                        <b-col sm="5">
                            <br>
                            <b-button type="submit" :disabled="load" variant="success" block pill>
                                <i class="fa fa-check"></i> Guardar <b-spinner v-if="load" small></b-spinner>
                            </b-button>
                        </b-col>
                    </b-row>
                </b-form>
                <b-alert show variant="info">
                    <i class="fa fa-exclamation-circle"></i> Verificar los datos antes de presionar <b>Guardar</b>, ya que después no se podrán realizar cambios.
                </b-alert>
            </b-modal>
            <!-- SELECCIONAR INFORMACIÓN DE PAQUETERIA -->
            <b-modal ref="modalPaqueteria" id="modal-paqueteria" title="información de paquetería" size="lg" hide-footer>
                <envio-remision :remisione_id="remisione_id" @savedEnvio="savedEnvio"></envio-remision>
            </b-modal>
        </div>
    </div>
</template>

<script>
    import formatNumber from '../../mixins/formatNumber';
    import rowClass from '../../mixins/rowClass';
    import setResponsables from '../../mixins/setResponsables';
    import EnvioRemision from './partials/EnvioRemision.vue';
    moment.locale('es');
    export default {
        components: { EnvioRemision },
        props: ['role_id'],
        mixins: [formatNumber,rowClass,setResponsables],
        data() {
            return {
                clientes: [],
                fields: [
                    { key: 'id', label: 'Folio' },
                    { key: 'fecha_creacion', label: 'Fecha de creación' },
                    { key: 'cliente_id', label: 'Cliente' },
                    { key: 'total', label: 'Salida' },
                    'pagos',
                    { key: 'total_devolucion', label: 'Devolución' },
                    { key: 'total_pagar', label: 'Pagar' },
                    { key: 'detalles', label: '' },
                    { key: 'responsable', label: '' },
                    { key: 'editar', label: '' }
                ],
                // fieldsDep: [
                //     {key: 'index', label: 'No.'},
                //     {key: 'created_at', label: 'Fecha de pago'},
                //     'pago'
                // ],
                num_remision: null,
                inicio: '0000-00-00',
                final: '0000-00-00',
                remisiones: [],
                remision: {},
                queryCliente: '',
                resultsClientes: [],
                total_salida: 0,
                total_devolucion: 0,
                total_pagar: 0,
                cliente_id: null,
                options: [
                    { value: null, text: 'Selecciona una opción', disabled: true },
                    { value: 'Terminado', text: 'Terminado'},
                    { value: 'Proceso', text: 'Entregado' },
                    { value: 'Iniciado', text: 'Iniciado' }
                ],
                estadoRemision: null,
                detalles: false,
                mostrarSalida: false,
                // mostrarPagos: false,
                // mostrarDevolucion: false,
                mostrarFinal: false,
                donaciones: [],
                fieldsDon: [
                    { key: 'isbn', label: 'ISBN' },
                    { key: 'titulo', label: 'Libro' },
                    { key: 'unidades', label: 'Unidades donadas' },
                    { key: 'created_at', label: 'Fecha' },
                    {key: 'entregado_por', label: 'Donación entregada por'}, 
                ],
                registros: [],
                devoluciones: [],
                total_pagos: 0,
                vendidos: [],
                fieldsP: [
                    {key: 'isbn', label: 'ISBN'}, 
                    'libro', 
                    {key: 'costo_unitario', label: 'Costo unitario'}, 
                    {key: 'unidades', label: 'Unidades'}, 
                    'subtotal',
                    {key: 'detalles', label: ''}
                ],
                fieldsD: [
                    {key: 'index', label: 'N.'},
                    {key: 'created_at', label: 'Fecha'},
                    {key: 'user_id', label: 'Pago realizado por'},
                    {key: 'entregado_por', label: 'Pago entregado por'}, 
                    'unidades', 'pago'
                ],
                load: false,
                estados: [
                    { value: null, text: 'Selecciona una opción', disabled: true },
                    { value: 'entregado', text: 'ENTREGADO' },
                    { value: 'pagado', text: 'PAGADO'},
                    { value: 'cancelado', text: 'CANCELADO'}
                ],
                total_donacion: 0,
                fechas: [],
                // fieldsFechas: [
                //     { key: 'fecha_devolucion', label: 'Fecha' },
                //     { key: 'entregado_por', label: 'Entregada por' },
                //     { key: 'isbn', label: 'ISBN' },
                //     { key: 'titulo', label: 'Libro' },
                //     'unidades', 'total'
                // ],
                depositos: [],
                comentarios: [],
                // newComment: false,
                // commentRem: {
                //     remision_id: null,
                //     comentario: ''
                // },
                stateDate: null,
                newEditRemision: false,
                listaRemisiones: true,
                // total_depositos: 0,
                total_vendido: 0,
                checkUnit: false,
                remisione_id: null,
                editar: false,
                datosRemision: {
                    id: null,
                    corte_id: null,
                    cliente: {},
                    fecha_entrega: '',
                    total: 0,
                    datos: [],
                    nuevos: [],
                    eliminados: []
                },
                position: null,

                // VARIABLES NUEVAS (OK)
                remisionesData: {},
                sTCliente: false,
                sTFecha: false,
                sTEstado: false,
                loadTotales: false,
                form: {
                    remisione_id: null,
                    responsable: null
                }
            }
        },
        mounted: function(){
            this.getResults();
        },
        filters: {
            moment: function (date) {
                return moment(date).format('DD-MM-YYYY');
            },
        },
        methods: {
            // OBTENER REMISIONES POR PAGINA
            getResults(page = 1){
                if(!this.sTCliente && !this.sTFecha && !this.sTEstado)
                   this.http_remisiones(page); 
                if(this.sTCliente)
                    this.http_cliente(page);
                if(this.sTFecha){
                    this.http_fecha(page);
                }
                if(this.sTEstado)
                    this.http_estado(page);
            },
            // HTTP OBTENER TODAS LAS REMSIONES
            http_remisiones(page = 1){
                this.load = true;
                axios.get(`/remisiones/index?page=${page}`).then(response => {
                    this.remisionesData = response.data; 
                    this.remisiones = response.data.data;
                    this.load = false;   
                }).catch(error => {
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                    this.load = false;
                });
            },
            set_totales(totales){
                this.total_salida = totales.total;
                this.total_devolucion = totales.total_devolucion;
                this.total_pagos = totales.total_pagos;
                this.total_pagar = totales.total_pagar;
            },
            // HTTP OBTENER POR CLIENTE
            http_cliente(page = 1){
                this.load = true;
                axios.get(`/buscar_por_cliente?page=${page}`, {params: {id: this.cliente_id}}).then(response => {
                    this.valores(response.data.remisiones, true, false, false);
                    this.set_totales(response.data.totales);
                    this.load = false;
                }).catch(error => {
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                    this.load = false;
                });
            },
            // HTTP OBTENER POR FECHA
            http_fecha(page = 1){
                this.load = true;
                axios.get(`/buscar_por_fecha?page=${page}`, {
                    params: {cliente_id: this.cliente_id, inicio: this.inicio, final: this.final}}).then(response => {
                    this.valores(response.data, false,true, false);
                    this.load = false;
                }).catch(error => {
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                    this.load = false;
                });
            },
            get_totales_fecha(){
                this.loadTotales = true;
                this.total_salida = 0;
                axios.get('/get_totales_fecha/', { params: {cliente_id: this.cliente_id, inicio: this.inicio, final: this.final}}).then(response => {
                    this.set_totales(response.data);
                    this.loadTotales = false;
                }).catch(error => {
                    // this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                    this.loadTotales = false;
                });
            },
            // HTTP OBTENER POR ESTADO
            http_estado(page = 1){
                this.load = true;
                axios.get(`/buscar_por_estado?page=${page}`, {params: {
                    estado: this.estadoRemision, cliente_id: this.cliente_id,}})
                .then(response => {
                    this.valores(response.data, false, false, true);
                    this.total_salida = 0;
                    this.load = false;
                }).catch(error => {
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                    this.load = false;
                });
            },
            // MOSTRAR REMISIONES POR CLIENTE
            porCliente(result){
                this.cliente_id = result.id;
                this.queryCliente = result.name;
                this.inicio = '0000-00-00';
                this.final = '0000-00-00';
                this.estadoRemision = null;
                this.num_remision = null;
                this.resultsClientes = [];
                this.estadoRemision = null;
                this.http_cliente();
            },
            // OBTENER REMISIONES POR FECHA
            porFecha(){
                if(this.final != '0000-00-00'){
                    if(this.inicio != '0000-00-00'){
                        // var fecha1 = moment(this.inicio);
                        // var fecha2 = moment(this.final);
                        // if(fecha2.diff(fecha1, 'days') <= 31) {
                            this.num_remision = null;
                            this.estadoRemision = null;
                            this.http_fecha();
                            this.get_totales_fecha();
                        // } else {
                        //     this.makeToast('warning', 'La fecha DE y la fecha A solo puede tener máximo un mes de diferencia.');
                        // }
                    } else {
                        this.stateDate = false;
                        this.makeToast('warning', 'Es necesario seleccionar la fecha de inicio');
                    }
                }
            },
            // MOSTRAR REMISIONES POR ESTADO
            porEstado(){
                this.num_remision = null;
                this.inicio = '0000-00-00';
                this.final = '0000-00-00';
                this.http_estado();
            },
            // AISGNAR LOS DATOS DE BUSQUEDA A VARIABLES
            valores(response, tCliente, tFecha, tEstado){
                this.remisionesData = response;
                this.remisiones = response.data;
                this.sTCliente = tCliente;
                this.sTFecha = tFecha;
                this.sTEstado = tEstado;
            },
            // // INICIALIZAR PARA NUEVA REMISIÓN
            // nuevaRemision(){
            //     axios.get('/getTodo').then(response => {
            //         this.datosNewEdit(false, null, response.data, false, true);
            //     }).catch(error => {
            //        this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
            //     });
            // },
            // // EDITAR REMISION
            // editarRemision(remision, index){
            //     axios.get('/get_remision_id', {params: {id: remision.id}}).then(response => {
            //         this.datosNewEdit(true, response.data.remision, response.data.clientes, false, true);
            //         this.position = index;
            //     }).catch(error => {
            //         this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
            //     });
            // },
            // // INICIALIZACIÓN DE LOS DATOS PARA CREAR / EDITAR LA REMISIÓN
            // datosNewEdit(editar, remision, clientes, listaRemisiones, newEditRemision){
            //     this.editar = editar;
            //     this.clientes = clientes;
            //     this.listaRemisiones = listaRemisiones;
            //     this.newEditRemision = newEditRemision;

            //     if(!this.editar){
            //         this.datosRemision = {
            //             id: null,
            //             corte_id: null,
            //             cliente: {},
            //             fecha_entrega: '',
            //             total: 0,
            //             datos: [],
            //             nuevos: [],
            //             eliminados: []
            //         };
            //     } else {
            //         this.datosRemision = {
            //             id: remision.id,
            //             corte_id: remision.corte_id,
            //             cliente: remision.cliente,
            //             fecha_entrega: remision.fecha_entrega,
            //             total: remision.total,
            //             datos: remision.datos,
            //             nuevos: [],
            //             eliminados: []
            //         };
            //     }
            // },
            selectResponsable(remision, i){
                this.load = true;
                this.options = [];
                axios.get('/remisiones/get_responsables').then(response => {
                    this.assign_responsables(this.options, response.data);
                    this.form.remisione_id = remision.id;
                    this.position = i;
                    this.$refs['modalMarcarE'].show();
                    this.load = false;
                }).catch(error => {
                    this.load = false;
                });
            },
            selectPaqueteria(remision, i){
                this.remisione_id = remision.id;
                this.position = i;
                this.$refs['modalPaqueteria'].show();
            },
            saveResponsable(){
                this.load = true;
                axios.post('/remisiones/save_responsable', this.form).then(response => {
                    this.remisiones[this.position].responsable = response.data.responsable;
                    this.makeToast('success', 'El responsable de la entrega se guardo correctamente.');
                    this.$refs['modalMarcarE'].hide();
                    this.load = false;
                }).catch(error => {
                    this.load = false;
                });
            },
            savedEnvio(remision){
                this.remisiones[this.position].paqueteria_id = remision.paqueteria_id;
                this.makeToast('success', 'Los datos se guardaron correctamente.');
                this.$refs['modalPaqueteria'].hide();
            },
            // AGREGAR LA NUEVA REMISIÓN A LA LISTA
            actualizarRs(remision){
                if(!this.editar){
                    this.http_remisiones(1); 
                    this.makeToast('success', 'La remisión se creó correctamente.');
                } else {
                    this.remisiones[this.position] = remision;
                    this.makeToast('success', 'La remisión se actualizo correctamente.');
                }
                // this.acumular();
                this.newEditRemision = false;
                this.listaRemisiones = true;
            },
            // GUARDAR COMENTARIO DE LA REMISIÓN
            // guardarComentario() {
            //     this.commentRem.remision_id = this.remision.id;
            //     this.load = true;
            //     axios.post('/guardar_comentario', this.commentRem).then(response => {
            //         this.load = false;
            //         this.makeToast('success', 'El comentario se guardo correctamente');
            //         this.comentarios.push(response.data);
            //         this.ini_comment();
            //     })
            //     .catch(error => {
            //         this.load = false;
            //         this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
            //     });
            // },
            // Inicializar las variables para agregar un nuevo comentario
            // ini_comment(){
            //     this.newComment = false;
            //     this.commentRem = {
            //         remision_id: null,
            //         comentario: ''
            //     }
            // },
            // CONSULTAR REMISIÓN POR NUMERO
            porNumero(){
                if(this.num_remision > 0){
                    this.load = true;
                    axios.get('/buscar_por_numero', {params: {num_remision: this.num_remision}}).then(response => {
                        if(response.data.remision){
                            this.remisionesData = {};
                            this.remisiones = [];
                            this.remisiones.push(response.data.remision);
                            this.total_salida = 0;
                        } else {
                            this.makeToast('warning', 'El folio ingresado no existe.');
                        }
                        this.load = false;
                        // this.acumular();
                    }).catch(error => {
                        this.makeToast('danger', 'Error al consultar el numero de remisión ingresado.');
                        this.load = false;
                    });
                }
            },
            // MOSTRAR LOS CLIENTES
            mostrarClientes(){
                if(this.queryCliente.length > 0){
                    axios.get('/mostrarClientes', {params: {queryCliente: this.queryCliente}}).then(response => {
                        this.resultsClientes = response.data;
                    }); 
                }
                else{
                    this.resultsClientes = [];
                }
            },
            // // MOSTRAR LOS DETALLES DE LA REMISIÓN
            // detallesRemision(remision){
            //     this.mostrarSalida = false;
            //     this.mostrarPagos = false;
            //     this.mostrarDevolucion = false;
            //     this.mostrarFinal = false;
            //     this.total_depositos = 0;
            //     this.total_vendido = 0;
            //     this.checkUnit = false;
            //     axios.get('/lista_datos', {params: {numero: remision.id}}).then(response => {
            //         this.listaRemisiones = false;
            //         this.detalles = true;
            //         this.remision = remision;
            //         this.registros = response.data.remision.datos;
            //         this.devoluciones = response.data.remision.devoluciones;
            //         this.fechas = response.data.remision.fechas;
            //         this.donaciones = response.data.remision.donaciones;
            //         this.depositos = response.data.remision.depositos;
            //         this.comentarios = response.data.remision.comentarios;

            //         this.depositos.forEach(deposito => {
            //             this.total_depositos += deposito.pago;
            //         });

            //         var count = 0;
            //         this.vendidos.forEach(vendido => {
            //             if(vendido.unidades > 0){
            //                count += 1; 
            //             }
            //             this.total_vendido += vendido.total;
            //         });

            //         if(count > 0 && (this.total_depositos !== this.total_vendido)){
            //             this.checkUnit = true;
            //         }
            //     }).catch(error => {
            //         this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
            //     });
            // },
            // CANCELAR REMISIÓN
            // cambiarEstado(){
            //     this.load = true;
            //     axios.put('/remisiones/cancel', this.remision).then(response => {
            //         this.remision.estado = response.data.estado;
            //         this.$bvModal.hide('modal-cancelar');
            //         this.load = false;
            //         this.makeToast('secondary', 'Remisión cancelada');
            //     })
            //     .catch(error => {
            //         this.load = false;
            //         this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
            //     });
            // },
            // acumular(){
            //     this.total_salida = 0;
            //     this.total_devolucion = 0;
            //     this.total_pagos = 0;
            //     this.total_pagar = 0;
            //     this.remisiones.forEach(remision => {
            //         if(remision.estado == 'Proceso' || remision.estado == 'Terminado'){
            //             this.total_salida += remision.total;
            //             this.total_devolucion += remision.total_devolucion;
            //             this.total_pagos += remision.pagos;
            //             this.total_pagar += remision.total_pagar;
            //         }
            //     });
            // },
            makeToast(variant = null, descripcion) {
                this.$bvToast.toast(descripcion, {
                    title: 'Mensaje',
                    variant: variant,
                    solid: true
                })
            }
        }
    }
</script>

<style>
    #btnCancelar {
        color: red;
        background-color: transparent;
        border: 0ch;
        font-size: 25px;
    }
    #listaL{
        position: absolute;
        z-index: 100
    }
</style>