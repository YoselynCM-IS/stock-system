<template>
    <div>
        <check-connection-component></check-connection-component>
        <div v-if="!mostrarDetalles && !mostrarPagos">
            <!-- BUSCAR REMISIÓN POR CLIENTE -->
            <b-row class="col-md-6">
                <b-col sm="2">
                    <label for="input-cliente">Cliente</label>
                </b-col>
                <b-col sm="10">
                    <b-input v-model="queryCliente" @keyup="mostrarClientes()"
                        style="text-transform:uppercase;">
                    </b-input>
                    <div class="list-group" v-if="resultsClientes.length" id="listP">
                        <a 
                            href="#" 
                            v-bind:key="i" 
                            class="list-group-item list-group-item-action" 
                            v-for="(result, i) in resultsClientes" 
                            @click="pagosCliente(result)">
                            {{ result.name }}
                        </a>
                    </div>
                </b-col>
            </b-row>
            <hr>
            <b-row>
                <b-col sm="8">
                    <!-- PAGINACIÓN -->
                    <pagination size="default" :limit="1" :data="remisionesData" 
                        @pagination-change-page="getResults">
                        <span slot="prev-nav"><i class="fa fa-angle-left"></i></span>
                        <span slot="next-nav"><i class="fa fa-angle-right"></i></span>
                    </pagination>
                </b-col>
                <b-col sm="4" class="text-right">
                    <b-button href="/descargar_gralClientes" variant="dark"><i class="fa fa-download"></i> Descargar</b-button>
                </b-col>
            </b-row>
            <div v-if="!load">
                <b-table v-if="remisiones.length > 0"
                    :items="remisiones" :fields="fields" responsive hover>
                    <template v-slot:cell(index)="row">
                        {{ row.index + 1 }}
                    </template>
                    <template v-slot:cell(cliente)="row">{{ row.item.cliente.name }}</template>
                    <template v-slot:cell(total)="row">${{ row.item.total | formatNumber }}</template>
                    <template v-slot:cell(total_devolucion)="row">${{ row.item.total_devolucion | formatNumber }}</template>
                    <template v-slot:cell(total_pagar)="row">${{ row.item.total_pagar | formatNumber }}</template>
                    <template v-slot:cell(total_pagos)="row">${{ row.item.total_pagos | formatNumber }}</template>
                    <template v-slot:cell(ver_pagos)="row">
                        <b-button
                            v-if="row.item.total_devolucion > 0 || row.item.total_pagos > 0"
                            @click="verPagos(row.item)"
                            variant="info">Mostrar
                        </b-button>
                    </template>
                    <template v-slot:cell(pagar)="row">
                        <b-button 
                            v-if="row.item.total_pagar > 0 && (role_id === 1 || role_id === 2)"
                            @click="registrarDeposito(row.item, row.index)"
                            variant="primary">Registrar
                        </b-button>
                    </template>
                    <template #thead-top="row">
                        <tr>
                            <th colspan="2"></th>
                            <th>${{ total_salida | formatNumber }}</th>
                            <th>${{ total_pagos | formatNumber }}</th>
                            <th>${{ total_devolucion | formatNumber }}</th>
                            <th>${{ total_pagar | formatNumber }}</th>
                        </tr>
                    </template>
                </b-table>
                <b-alert v-else show variant="secondary">
                    <i class="fa fa-warning"></i> No se encontraron registros.
                </b-alert>
                <b-modal ref="modal-registrarDeposito" title="Registrar pago">
                    <b-form @submit.prevent="guardarDeposito()">
                        <b-row>
                            <b-col class="text-right" sm="3">
                                <label>Pago</label>
                            </b-col>
                            <b-col sm="7">
                                <b-form-input v-model="datoRem.monto" autofocus :state="state" :disabled="load" required></b-form-input>
                            </b-col>
                        </b-row>
                        <b-row>
                            <b-col class="text-right" sm="3">
                                <label>Fecha del pago</label>
                            </b-col>
                            <b-col sm="7">
                                <b-form-input v-model="datoRem.fecha"
                                    type="date" :disabled="load" required>
                                </b-form-input>
                            </b-col>
                        </b-row>
                        <b-row>
                            <b-col class="text-right" sm="3">
                                <label>Nota</label>
                            </b-col>
                            <b-col sm="7">
                                <b-form-textarea v-model="datoRem.nota" 
                                    :state="state" :disabled="load" required
                                    rows="6" max-rows="6">
                                </b-form-textarea>
                            </b-col>
                        </b-row>
                        <br>
                        <div class="text-right">
                            <b-button type="submit" variant="success" :disabled="load">
                                <i class="fa fa-check"></i> {{ !load ? 'Guardar' : 'Guardando' }} <b-spinner small v-if="load"></b-spinner>
                            </b-button>
                        </div>
                    </b-form>
                    <div slot="modal-footer">
                        <b-alert show variant="info">
                            <i class="fa fa-exclamation-circle"></i> Verificar el pago antes de presionar <b>Guardar</b>, ya que después <b>no se podrán realizar cambios</b>.
                        </b-alert>
                    </div>
                </b-modal>
            </div>
            <div v-else class="text-center text-info my-2 mt-3">
                <b-spinner class="align-middle"></b-spinner>
                <strong>Cargando...</strong>
            </div> 
        </div>
        <div v-if="mostrarPagos">
            <b-row>
                <b-col>
                    <h5><b>Cliente: {{ detailsRemC.cliente }}</b></h5>
                </b-col>
                <b-col sm="3">
                    <a class="btn btn-dark" :href="`/pagos/download_edocuenta/${detailsRemC.cliente_id}`">
                        <i class="fa fa-download"></i> Edo. Cuenta
                    </a>
                </b-col>
                <b-col sm="2">
                    <div class="text-right">
                        <b-button variant="secondary" @click="mostrarPagos = false">
                            <i class="fa fa-mail-reply"></i> Regresar
                        </b-button>
                    </div>
                </b-col>
            </b-row>
            <hr>
            <div v-if="detailsRemC.remdepositos.length > 0">
                <h5><b>Pagos de forma general</b></h5>
                <b-table :items="detailsRemC.remdepositos" :fields="fieldsRem">
                    <template v-slot:cell(index)="row">
                        {{ row.index + 1 }}
                    </template>
                    <template v-slot:cell(pago)="row">
                        ${{ row.item.pago | formatNumber }}
                    </template>
                    <template v-slot:cell(created_at)="row">
                        {{ row.item.created_at }}
                    </template>
                    <template v-slot:cell(revisado)="row">
                        <label v-if="row.item.revisado">
                            <i class="fa fa-check" style="color:green;"></i> Revisado el: {{ row.item.updated_at | moment }}
                        </label>
                        <b-button v-if="!row.item.revisado && role_id === 1" :disabled="load" variant="secondary" @click="marcarRevision(row.item, row.index)">
                            <i class="fa fa-check"></i> Marcar revisión
                        </b-button>
                    </template>
                </b-table>
            </div>
            <div v-if="detailsRemC.depositos.length > 0">
                <h5><b>Pagos por remisión</b></h5>
                <b-table :items="detailsRemC.depositos" :fields="fieldsDepositos">
                    <template v-slot:cell(index)="row">
                        {{ row.index + 1 }}
                    </template>
                    <template v-slot:cell(pago)="row">
                        ${{ row.item.pago | formatNumber }}
                    </template>
                    <template v-slot:cell(created_at)="row">
                        {{ row.item.created_at }}
                    </template>
                </b-table>
            </div>
            <b-modal ref="modal-check" centered size="sm" title="Marcar revisión" hide-footer>
                <div class="text-center">
                    <b-button :disabled="load" variant="success" @click="guardarRevision()">
                        <i class="fa fa-check"></i> CONFIRMAR
                    </b-button>
                </div>
            </b-modal>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['role_id'],
        data() {
            return {
                remisionesData: {},
                remisiones: [],
                fields: [
                    {key: 'index', label: 'N.'},
                    'cliente', 
                    {key: 'total', label: 'Salida'}, 
                    {key: 'total_pagos', label: 'Pagado'},
                    {key: 'total_devolucion', label: 'Devolución'}, 
                    {key: 'total_pagar', label: 'Pagar'}, 
                    {key: 'ver_pagos', label: 'Pago(s)'},
                    {key: 'pagar', label: ''}
                ],
                fieldsRP: [
                    {key: 'isbn', label: 'ISBN'}, 
                    'libro', 
                    {key: 'costo_unitario', label: 'Costo unitario'}, 
                    {key: 'unidades_resta', label: 'Unidades pendientes'},
                    {key: 'unidades_base', label: 'Unidades'}, 
                    'subtotal'
                ],
                fieldsP: [
                    {key: 'isbn', label: 'ISBN'}, 
                    'libro', 
                    {key: 'costo_unitario', label: 'Costo unitario'}, 
                    {key: 'unidades', label: 'Unidades vendidas'}, 
                    'subtotal', 'detalles'
                ],
                fieldsDep: [
                    {key: 'index', label: 'No.'},
                    {key: 'created_at', label: 'Fecha de pago'},
                    {key: 'user', label: 'Pago realizado por'},
                    'pago'
                ],
                fieldsD: [
                    {key: 'index', label: 'N.'},
                    {key: 'created_at', label: 'Fecha'},
                    {key: 'user_id', label: 'Pago realizado por'},
                    {key: 'entregado_por', label: 'Pago entregado por'}, 
                    'unidades', 'pago'
                ],
                fieldsRem: [
                    {key: 'index', label: 'N.'},
                    {key: 'created_at', label: 'Fecha de registro'}, 'pago',
                    {key: 'ingresado_por', label: 'Ingresado por'},
                    'nota',
                    {key: 'fecha', label: 'Fecha del pago'},
                    {key: 'revisado', label: ''},
                    
                ],
                fieldsDepositos: [
                    {key: 'index', label: 'No.'},
                    { key: 'created_at', label: 'Fecha de registro' },
                    { key: 'remisione_id', label: 'Folio' },
                    'pago',
                    {key: 'ingresado_por', label: 'Ingresado por'}
                ],
                mostrarDetalles: false,
                remision: {
                    id: 0,
                    cliente: {},
                    pagos: 0,
                    total_pagar: 0,
                    unidades: 0,
                    datos: [],
                    vendidos: [],
                    depositos: []
                },
                btnGuardar: false,
                pos_remision: 0,
                mostrarPagos: false,
                state: null,
                stateU: null,
                load: false,
                deposito: {
                    remision_id: 0,
                    pago: null
                },
                queryCliente: '',
                resultsClientes: [],
                remision_id: null,
                loadRegisters: false,
                checkUnit: false,
                total_unidades: 0,
                total_vendido: 0,
                datoRem: {
                    cliente_id: null,
                    cliente: '',
                    total: 0,
                    total_devolucion: 0,
                    total_pagos: 0,
                    total_pagar: 0,
                    monto: 0,
                    unidades: 0,
                    depositos: [],
                    entregado_por: null,
                    nota: null,
                    fecha: null
                },
                entregado_por: null,
                stateResp: null,
                options: [],
                total_salida: 0,
                total_pagos: 0,
                total_devolucion: 0,
                total_pagar: 0,
                checkDep: {},
                cliente_id: null,
                detailsRemC: {
                    cliente: null,
                    remdepositos: [],
                    depositos: []
                }
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
            this.acumular_totales();
        },
        methods: {
            // OBTENER REMISIONES POR PAGINA
            getResults(page = 1){
                this.load = true;
                axios.get(`/remcliente/index?page=${page}`).then(response => {
                    this.remisionesData = response.data; 
                    this.remisiones = response.data.data;
                    this.load = false;   
                }).catch(error => {
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                    this.load = false;
                });
            },
            // OBTENER TOTALES DE TODO 
            acumular_totales(){
                this.load = false;
                axios.get('/remcliente/get_totales').then(response => {
                    this.set_totales(response.data[0]);
                    this.load = false;   
                }).catch(error => {
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                    this.load = false;
                });
            },
            set_totales(r){
                this.total_salida       = r.total;
                this.total_devolucion   = r.total_devolucion;
                this.total_pagos        = r.total_pagos;
                this.total_pagar        = r.total_pagar;
            },
            // MARCAR REVISIÓN DEL PAGO
            marcarRevision(deposito, posicion){
                this.checkDep.id = deposito.id;
                this.checkDep.posicion = posicion;
                this.$refs['modal-check'].show();
            },
            // GUARDAR LA REVISIÓN DEL DEPOSITO
            guardarRevision(){
                this.load = true;
                axios.put('/guardar_revision', this.checkDep).then(response => {
                    this.detailsRemC.remdepositos[this.checkDep.posicion].revisado = true;
                    this.makeToast('success', 'La revisión se guardo correctamente.');
                    this.load = false;
                    this.$refs['modal-check'].hide();
                }).catch(error => {
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                    this.load = false;
                });
            },
            // BUSCAR REMISIÓN POR NUMERO
            porNumero(){
                if(this.remision_id > 0){
                    axios.get('/buscar_por_numero', {params: {num_remision: this.remision_id}}).then(response => {
                        if(response.data.remision.estado == 'Iniciado')
                            this.makeToast('warning', 'La remisión aún no ha sido marcada como entregada.');
                        if(response.data.remision.estado == 'Cancelado')
                            this.makeToast('warning', 'La remisión esta cancelada');
                        if(response.data.remision.total_pagar == 0 && (response.data.remision.estado == 'Proceso' || response.data.remision.estado == 'Terminado'))
                            this.makeToast('warning', 'La remisión ya se encuentra pagada. Consultar en el apartado de remisiones');
                        if(response.data.remision.total_pagar > 0){
                            this.remisiones = [];
                            this.remisiones.push(response.data.remision);
                        }
                    }).catch(error => {
                        this.makeToast('danger', 'Error al consultar el numero de remisión ingresado');
                    });
                }
            },
            // MOSTRAR CLIENTES POR COINCIDENCIA
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
            // MOSTRAR PAGOS DEL CLIENTE
            pagosCliente(cliente){
                this.resultsClientes = [];
                this.queryCliente = cliente.name;
                axios.get('/remcliente/by_cliente', {params: {cliente_id: cliente.id}}).then(response => {
                    if(response.data.cliente){
                        this.remisionesData = {};
                        this.remisiones = [];
                        this.remisiones.push(response.data);
                        this.set_totales(this.remisiones[0]);
                    } else {
                        this.makeToast('warning', 'El cliente seleccionado no tiene una cuenta general registrada');
                    }
                }).catch(error => {
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                });
            },
            // INICIALIZAR PARA REGISTRAR PAGO
            registrarDeposito(remcliente, index){
                axios.get('/get_remcliente', {params: {cliente_id: remcliente.cliente_id}}).then(response => {
                    if(response.data.total_pagar > 0){
                        this.pos_remision = index;
                        this.assign_rem(response.data, '', []);
                        this.state = null;
                        this.$refs['modal-registrarDeposito'].show();
                    } else {
                        this.makeToast('warning', 'El cliente ya no tiene saldo por pagar. Actualizar la pagina para mostrar los cambios.');
                    }
                }).catch(error => {
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                });
            },
            assign_rem(remcliente, name, depositos){
                this.datoRem = {
                    cliente_id: remcliente.cliente_id,
                    cliente: name,
                    total: remcliente.total,
                    total_devolucion: remcliente.total_devolucion,
                    total_pagos: remcliente.total_pagos,
                    total_pagar: remcliente.total_pagar,
                    monto: 0,
                    unidades: 0,
                    depositos: depositos,
                    entregado_por: null,
                    fecha: null
                };
            },
            // LISTAR TODOS LOS PAGOS REALIZADOS
            verPagos(remision){
                axios.get('/pagos/depositos_cliente', {params: {id: remision.id, cliente_id: remision.cliente_id}}).then(response => {
                    // this.assign_rem(response.data.remcliente, response.data.remcliente.cliente.name, response.data.remcliente.remdepositos);
                    this.detailsRemC = {
                        cliente_id: remision.cliente_id,
                        cliente: remision.cliente.name,
                        remdepositos: response.data.remdepositos,
                        depositos: response.data.depositos
                    };
                    this.mostrarPagos = true;
                }).catch(error => {
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                });
            },
            // GIUARDAR DEPOSITO
            guardarDeposito(){
                if(this.datoRem.monto > 0){
                    if(this.datoRem.monto <= this.datoRem.total_pagar){
                        this.state = true;
                        this.load = true; 
                        axios.post('/pagos/store_gral', this.datoRem).then(response => {
                            this.remisiones[this.pos_remision].total_pagos = response.data.total_pagos;
                            this.remisiones[this.pos_remision].total_pagar = response.data.total_pagar;
                            this.makeToast('success', 'El pago se guardo correctamente');
                            this.load = false;
                        }).catch(error => {
                            this.load = false;
                            this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                        });
                    }
                    else{
                        this.state = false;
                        this.makeToast('warning', 'El pago es mayor al total a pagar');
                    }
                }
                else{
                    this.state = false;
                    this.makeToast('warning', 'El pago tiene que ser mayor a 0');
                }
            },
            proccess_deposito(){
                if(this.datoRem.unidades > 0){
                    
                } else {
                    this.stateU = false;
                    this.makeToast('warning', 'Introducir el número de unidades que se pagaran.');
                }
            },
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
    #listP{
        position: absolute;
        z-index: 100
    }
</style>