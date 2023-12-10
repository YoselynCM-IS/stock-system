<template>
    <div>
        <check-connection-component></check-connection-component>
        <div v-if="!mostrarDevolucion && !mostrarPagos">
            <b-row>
                <!-- BUSCAR REMISION POR NUMERO -->
                <b-col sm="4"> 
                    <b-row class="my-1">
                        <b-col sm="4">
                            <label for="input-numero">Remisión</label>
                        </b-col>
                        <b-col sm="8">
                            <b-form-input 
                                id="input-numero" 
                                type="number" 
                                v-model="num_remision" 
                                @keyup.enter="porNumero()">
                            </b-form-input>
                        </b-col>
                    </b-row>
                </b-col>
                <!-- BUSCAR REMISION POR CLIENTE -->
                <b-col sm="6">
                    <b-row class="my-1">
                        <b-col sm="2">
                            <label for="input-cliente">Cliente</label>
                        </b-col>
                        <b-col sm="10">
                            <b-input v-model="queryCliente" @keyup="mostrarClientes()"
                                style="text-transform:uppercase;"
                            ></b-input>
                            <div class="list-group" v-if="resultsClientes.length" id="listaD">
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
                </b-col>
                <b-col sm="2">
                    <!-- <b-button variant="info" pill v-b-modal.modal-ayudaAP><i class="fa fa-info-circle"></i> Ayuda</b-button> -->
                </b-col>
            </b-row> 
            <hr>
            <!-- PAGINACIÓN -->
            <pagination size="default" :limit="1" :data="remisionesData" 
                @pagination-change-page="getResults">
                <span slot="prev-nav"><i class="fa fa-angle-left"></i></span>
                <span slot="next-nav"><i class="fa fa-angle-right"></i></span>
            </pagination>
            <div v-if="!load">
                <!-- TABLA DE REMISIONES -->
                <b-table v-if="remisiones.length > 0" responsive hover
                    :items="remisiones" :fields="fields">
                    <template v-slot:cell(cliente)="row">{{ row.item.cliente.name }}</template>
                    <template v-slot:cell(total)="row">${{ row.item.total | formatNumber }}</template>
                    <template v-slot:cell(total_devolucion)="row">${{ row.item.total_devolucion | formatNumber }}</template>
                    <template v-slot:cell(pagos)="row">${{ row.item.pagos | formatNumber }}</template>
                    <template v-slot:cell(total_pagar)="row">${{ row.item.total_pagar | formatNumber }}</template>
                    <!-- <template v-slot:cell(registrar_pago)="row">
                        <b-button 
                            v-if="row.item.total_pagar > 0 && (role_id == 3 || role_id == 6)"
                            variant="primary" 
                            @click="registrarPago(row.item, row.index)">Pago
                        </b-button>
                    </template> -->
                    <template v-slot:cell(registrar_devolucion)="row">
                        <!-- OMEGA BOOK / MODIFICAR CLIENTE_ID (ESTO ES DESDE MAJESTIC EDUCATION)-->
                        <!-- <b-button v-if="(row.item.cliente_id !== 288 && row.item.cliente.name !== 'OMEGA BOOK')
                             && (row.item.total_pagar > 0 && (role_id == 1 || role_id == 3 || role_id == 6))" 
                            variant="dark" 
                            @click="registrarDevolucion(row.item, row.index)">Devolución
                        </b-button> -->
                        <b-button 
                            v-if="row.item.total_pagar > 0 && (role_id == 1 || role_id == 3 || role_id == 6)" 
                            variant="dark" 
                            @click="registrarDevolucion(row.item, row.index)">Devolución
                        </b-button>
                    </template>
                    <template v-slot:cell(cerrar_remision)="row">
                        <!-- OMEGA BOOK / MODIFICAR CLIENTE_ID (ESTO ES DESDE MAJESTIC EDUCATION)-->
                        <!-- <b-button v-if="(row.item.cliente_id !== 288 && row.item.cliente.name !== 'OMEGA BOOK')
                            && row.item.total_pagar > 0 && (role_id == 1 || role_id == 2 || role_id == 6)" 
                            @click="cerrarRemision(row.item, row.index)"
                            variant="secondary">Cerrar
                        </b-button>  -->
                        <b-button 
                            v-if="row.item.total_pagar > 0 && (role_id == 1 || role_id == 2 || role_id == 6)" 
                            @click="cerrarRemision(row.item, row.index)"
                            variant="secondary">Cerrar
                        </b-button> 
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
            <b-modal ref="modal-registrar-deposito" title="Registrar pago">
                <b-form @submit.prevent="guardarMonto()">
                    <b-row>
                        <b-col class="text-right" sm="3">
                            <label>Pago</label>
                        </b-col>
                        <b-col sm="7">
                            <b-form-input v-model="deposito.monto" autofocus :state="stateM" :disabled="load" required></b-form-input>
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col class="text-right" sm="3">
                            <label>Recibí de</label>
                        </b-col>
                        <b-col sm="7">
                            <b-form-select :state="stateR" :disabled="load" v-model="deposito.entregado_por" :options="options"></b-form-select>
                        </b-col>
                    </b-row>
                    <hr>
                    <div class="text-center">
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
        <!-- REGISTRAR PAGO -->
        <div v-if="mostrarPagos">
            <h4 style="color: #170057">Registro de pago</h4>
            <hr>
            <b-row>
                <b-col sm="6"><h5><b>Remisión No. {{ remision.id }}</b></h5></b-col>
                <b-col sm="3">
                    <div class="text-right">
                        <b-button 
                            :disabled="load" 
                            variant="success"
                            @click="confirmarPagoU()">
                            <i class="fa fa-check"></i> {{ !load ? 'Guardar' : 'Guardando' }} <b-spinner small v-if="load"></b-spinner>
                        </b-button>
                    </div>
                </b-col>
                <b-col sm="3">
                    <div class="text-right">
                        <b-button variant="secondary" @click="mostrarPagos = false">
                            <i class="fa fa-mail-reply"></i> Regresar
                        </b-button>
                    </div>
                </b-col>
            </b-row>
            <label><b>Cliente:</b> {{ remision.cliente.name }}</label>
            <hr>
            <b-row>
                <b-col sm="2">Pago entregado por</b-col>
                <b-col sm="4"><b-form-select :state="state" v-model="entregado_por" :options="options"></b-form-select></b-col>
            </b-row><br>
            <b-table :items="remision.vendidos" :fields="fieldsRP">
                <template v-slot:cell(isbn)="row">{{ row.item.libro.ISBN }}</template>
                <template v-slot:cell(libro)="row">{{ row.item.libro.titulo }}</template>
                <template v-slot:cell(costo_unitario)="row">${{ row.item.dato.costo_unitario | formatNumber }}</template>
                <template v-slot:cell(unidades_base)="row">
                    <b-input 
                        :id="`inpVend-${row.index}`"
                        type="number" 
                        :disabled="load"
                        @change="verificarUnidades(row.item.unidades_base, row.item.unidades_resta, row.item.dato.costo_unitario, row.index)" 
                        v-model="row.item.unidades_base"> 
                    </b-input>
                </template>
                <template v-slot:cell(subtotal)="row">${{ row.item.total_base | formatNumber }}</template>
                <template #thead-top="row">
                    <tr>
                        <th colspan="5"></th>
                        <th>${{ total_vendido | formatNumber }}</th>
                    </tr>
                </template>
            </b-table>
            <!-- MODAL PARA CONFIRMAR PAGO -->
            <b-modal ref="modal-confirmarPagoU" size="xl" title="Resumen del pago">
                <h5><b>Remisión No. {{ remision.id }}</b></h5>
                <label><b>Cliente:</b> {{ remision.cliente.name }}</label><br>
                <label><b>Pago entregado por:</b> {{ entregado_por }}</label>
                <b-table :items="remision.vendidos" :fields="fieldsR">
                    <template v-slot:cell(isbn)="row">{{ row.item.libro.ISBN }}</template>
                    <template v-slot:cell(libro)="row">{{ row.item.libro.titulo }}</template>
                    <template v-slot:cell(costo_unitario)="row">${{ row.item.dato.costo_unitario | formatNumber }}</template>
                    <template v-slot:cell(subtotal)="row">${{ row.item.total_base | formatNumber }}</template>
                    <template #thead-top="row">
                        <tr>
                            <th colspan="4"></th>
                            <th>${{ total_vendido | formatNumber }}</th>
                        </tr>
                    </template>
                </b-table>
                <div slot="modal-footer">
                    <b-row>
                        <b-col sm="9">
                            <b-alert show variant="info">
                                <i class="fa fa-exclamation-circle"></i> <b>Verificar el pago.</b> En caso de algún error, modificar antes de presionar <b>Confirmar</b> ya que después no se podrán realizar cambios.
                            </b-alert>
                        </b-col>
                        <b-col sm="3" align="right">
                            <b-button 
                                :disabled="load" 
                                variant="success"
                                @click="guardarPago()">
                                <i class="fa fa-check"></i> Confirmar
                            </b-button>
                        </b-col>
                    </b-row>
                </div>
            </b-modal>
        </div>
        <!-- REGISTRAR DATOS DE DEVOLUCION -->
        <div v-if="mostrarDevolucion">
            <h4 style="color: #170057">Registro de devolución</h4>
            <hr>
            <div class="row">
                <div class="col-md-6"><h5><b>Remisión No. {{ remision.id }}</b></h5></div>
                <div class="col-md-3 text-right">
                    <b-button 
                        :disabled="load" 
                        variant="success" 
                        @click="confirmarDevolucion()">
                        <i class="fa fa-check"></i> {{ !load ? 'Guardar' : 'Guardando' }} <b-spinner small v-if="load"></b-spinner>
                    </b-button>
                    <!-- MODAL -->
                    <b-modal ref="modal-confirmarDevolucion" size="xl" title="Resumen de la devolución">
                        <h5><b>Remisión No. {{ remision.id }}</b></h5>
                        <label><b>Cliente:</b> {{ remision.cliente.name }}</label><br>
                        <label><b>Devolución entregada por:</b> {{ entregado_por }}</label>
                        <b-table :items="devoluciones" :fields="fieldsRP">
                            <template v-slot:cell(costo_unitario)="row">${{ row.item.dato.costo_unitario | formatNumber }}</template>
                            <template v-slot:cell(subtotal)="row">${{ row.item.total_base | formatNumber }}</template>
                            <template v-slot:cell(unidades_resta)="row">{{ row.item.unidades_resta | formatNumber }}</template>
                            <template v-slot:cell(defectuosos)="row">
                                {{ row.item.defectuosos | formatNumber }}
                                <b-button v-if="row.item.defectuosos > 0" v-b-tooltip.hover :title="row.item.comentario" variant="link" size="sm" pill>
                                    <i class="fa fa-info"></i>
                                </b-button>
                            </template>
                            <template #thead-top="row">
                                <tr>
                                    <th colspan="6"></th>
                                    <th>${{ total_devolucion | formatNumber }}</th>
                                </tr>
                            </template>
                        </b-table>
                        <div slot="modal-footer">
                            <b-row>
                                <b-col sm="9">
                                    <b-alert show variant="info">
                                        <i class="fa fa-exclamation-circle"></i> <b>Verificar la devolución.</b> En caso de algún error, modificar antes de presionar <b>Confirmar</b> ya que después no se podrán realizar cambios.
                                    </b-alert>
                                </b-col>
                                <b-col sm="3" align="right">
                                    <b-button 
                                        :disabled="load" 
                                        variant="success"
                                        @click="guardar()">
                                        <i class="fa fa-check"></i> Confirmar
                                    </b-button>
                                </b-col>
                            </b-row>
                        </div>
                    </b-modal>
                </div>
                <div class="col-md-3 text-right">
                    <b-button variant="secondary" @click="mostrarDevolucion = false">
                        <i class="fa fa-mail-reply"></i> Regresar
                    </b-button>
                </div>
            </div>
            <label><b>Cliente:</b> {{ remision.cliente.name }}</label>
            <hr>
            <b-row>
                <b-col sm="3">Devolución entregada por</b-col>
                <b-col sm="4"><b-form-select :state="state" v-model="entregado_por" :options="options"></b-form-select></b-col>
            </b-row><br>
            <b-table :items="devoluciones" :fields="fieldsRP">
                <template v-slot:cell(costo_unitario)="row">${{ row.item.dato.costo_unitario | formatNumber }}</template>
                <template v-slot:cell(subtotal)="row">${{ row.item.total_base | formatNumber }}</template>
                <template v-slot:cell(unidades_resta)="row">{{ row.item.unidades_resta | formatNumber }}</template>
                <template v-slot:cell(defectuosos)="row">
                    {{ row.item.defectuosos | formatNumber }}
                    <b-button v-if="row.item.defectuosos > 0" v-b-tooltip.hover :title="row.item.comentario" variant="link" size="sm" pill>
                        <i class="fa fa-info"></i>
                    </b-button>
                </template>
                <template #thead-top="row">
                    <tr>
                        <th colspan="6"></th>
                        <th>${{ total_devolucion | formatNumber }}</th>
                    </tr>
                </template>
                <template v-slot:cell(unidades_base)="row">
                    <div v-if="row.item.libro.type !== 'digital' ||
                        (row.item.libro.type == 'digital' && row.item.dato.codes.length == 0)">
                        <b-input v-if="row.item.status"
                            :id="`inpDev-${row.index}`" type="number" 
                            v-model="row.item.unidades_base" :disabled="load"
                            @change="guardarUnidades(row.item, row.index)"/>
                        <label v-else>
                            {{ row.item.unidades_base }}
                        </label>
                    </div>
                    <div v-if="row.item.libro.type == 'digital' && row.item.dato.codes.length > 0">
                        <b-input v-if="showSelectUnit && position == row.index" 
                            v-model="row.item.unidades_base" :disabled="load"
                            @change="guardarUnidades(row.item, row.index)"/>
                        <label v-else>
                            {{ row.item.unidades_base }}
                        </label>
                    </div>
                </template>
                <template v-slot:cell(actions)="row">
                    <div v-if="row.item.libro.type == 'digital' && row.item.unidades_resta > 0 && row.item.dato.codes.length > 0">
                        <b-button v-if="row.item.referencia != null" 
                                pill small block variant="info" @click="selectUnidades(row.item, row.index)">
                            Scratch
                        </b-button>
                        <b-button pill small block variant="info" @click="selectCodigos(row.item, row.index)">
                            Códigos
                        </b-button>
                    </div>
                    <b-button v-if="row.item.libro.type == 'venta'" :disabled="row.item.unidades_base <= 0"
                        pill small block variant="secondary" @click="addDefectuosos(row.item, row.index)">
                        Defectuosos
                    </b-button>
                </template>
            </b-table>
        </div>
        <!-- MODAL PARA SELECCIONAR CODIGOS -->
        <b-modal id="modal-select-codes" title="Seleccionar códigos" hide-footer>
            <b-table :items="codes" :fields="fieldsCodes" responsive
                :select-mode="selectMode" ref="selectableTable"
                selectable @row-selected="onRowSelected">
                <template v-slot:cell(index)="row">
                    {{ row.index + 1 }}
                </template>
            </b-table>
            <div class="text-right">
                <b-button :disabled="selected.length == 0" variant="success" 
                    pill @click="guardarCodes()">
                    <i class="fa fa-check"></i> Guardar
                </b-button>
            </div>
        </b-modal>
        <b-modal id="modal-add-defectuosos" title="Registrar defectuosos" hide-footer size="sm">
            <add-defectuosos-component @saveDefectuosos="saveDefectuosos"></add-defectuosos-component>
        </b-modal>
    </div>
</template>

<script>
import AddDefectuososComponent from '../libros/AddDefectuososComponent.vue';
    export default {
        props: ['listresponsables', 'role_id'],
        components: { AddDefectuososComponent },
        data() {
            return {
                fields: [
                    {key: 'id', label: 'Folio'}, 
                    'cliente', 
                    {key: 'total', label: 'Salida'}, 
                    {key: 'pagos', label: 'Pagado'},
                    {key: 'total_devolucion', label: 'Devolución'},
                    {key: 'total_pagar', label: 'Pagar'},
                    // {key: 'registrar_pago', label: ''},
                    {key: 'registrar_devolucion', label: ''},
                    {key: 'cerrar_remision', label: ''}
                ], // Columnas de la tabla principal donde se muestran las remisiones
                fieldsRP: [
                    { key: 'libro.ISBN', label: 'ISBN' }, 
                    { key: 'libro.titulo', label: 'Titulo' }, 
                    { key: 'costo_unitario', label: 'Costo unitario' }, 
                    { key: 'unidades_resta', label: 'Unidades pendientes' },
                    { key: 'defectuosos', label: 'Unidades defectuosos' },
                    { key: 'unidades_base', label: 'Unidades devolución' }, 
                    { key: 'subtotal', label: 'Total' },
                    { key: 'actions', label: '' },
                ], // Columnas donde se muestran los datos de las remisiones
                fieldsR: [
                    {key: 'isbn', label: 'ISBN'}, 
                    'libro', 
                    {key: 'costo_unitario', label: 'Costo unitario'},
                    {key: 'unidades_base', label: 'Unidades'}, 
                    'subtotal'
                ],
                mostrarDevolucion: false, // Indicar si se muestra el apartado para registrar devolución
                remision: {}, //Datos de la remision
                devoluciones: [], //Array de las devoluciones
                total_devolucion: 0,
                remisiones: [],
                num_remision: null,
                queryCliente: '',
                resultsClientes: [],
                pos_remision: 0,
                mostrarPagos: false,
                load: false,
                total_vendido: 0,
                pagoRemision: {},
                devolucionRemision: {},
                options: [],
                entregado_por: null,
                state: null,
                stateM: null,
                deposito: {
                    remision_id: null,
                    monto: 0,
                    total_pagar: 0,
                    pagar_restante: 0,
                    entregado_por: null,
                    posicion: null
                },
                stateR: null,
                stateU: null,
                remisionesData: {},
                cliente_id: null,
                selectMode: 'multi',
                selected: [],
                fieldsCodes: [
                    {key: 'index', label: 'N.'}, 
                    {key: 'codigo', label: 'Código'}, 
                ],
                position: null,
                codes: [],
                showSelectUnit: false,
                posD: null,
                devolucionD: {} 
            }
        },
        filters: {
            formatNumber: function (value) {
                return numeral(value).format("0,0[.]00"); 
            }
        },
        created: function(){
            this.getResults();
            this.assign_responsables();
        },
        methods: {
            // OBTENER REMISIONES POR PAGINA
            getResults(page = 1){
                if(this.cliente_id == null)
                    this.http_remisiones(page);
                else
                    this.http_cliente(page); 
            },
            // HTTP REMISIONES
            http_remisiones(page = 1){
                this.load = true;
                axios.get(`/remisiones/pay_remisiones?page=${page}`).then(response => {
                    this.remisionesData = response.data; 
                    this.remisiones = response.data.data;
                    this.load = false;   
                }).catch(error => {
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                    this.load = false;
                });
            },
            // HTTP CLIENTE
            http_cliente(page = 1){
                this.load = true;
                axios.get(`/pagos_remision_cliente?page=${page}`, {params: {cliente_id: this.cliente_id}}).then(response => {
                    this.remisionesData = response.data;
                    this.remisiones = response.data.data;
                    this.load = false;
                }).catch(error => {
                    this.load = false;
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                });
            },
            // MOSTRAR LOS PAGOS QUE HA REALIZADO EL CLIENTE
            porCliente(cliente){
                this.cliente_id = cliente.id;
                this.resultsClientes = [];
                this.queryCliente = cliente.name;
                this.http_cliente();
            },
            assign_responsables(){
                this.options.push({
                    value: null,
                    text: 'Selecciona una opción',
                    disabled: true
                });
                this.options.push({
                    value: 'CLIENTE',
                    text: 'CLIENTE'
                });
                this.listresponsables.forEach(responsable => {
                    if(responsable.responsable !== 'ARTURO'){
                        this.options.push({
                            value: responsable.responsable,
                            text: responsable.responsable
                        });
                    }
                });
            },
            // BUSCAR REMISIÓN POR NUMERO
            porNumero(){
                if(this.num_remision > 0){
                    axios.get('/buscar_por_numero', {params: {num_remision: this.num_remision}}).then(response => {
                        // if(response.data.remision.estado == 'Iniciado')
                        //     this.makeToast('warning', 'La remisión aún no ha sido marcada como entregada.');
                        if(response.data.remision.estado == 'Cancelado')
                            this.makeToast('warning', 'La remisión esta cancelada.');
                        if(response.data.remision.total_pagar == 0 && (response.data.remision.estado == 'Proceso' || response.data.remision.estado == 'Terminado'))
                            this.makeToast('warning', 'La remisión ya se encuentra pagada. Consultar en el apartado de remisiones.');
                        if(response.data.remision.total_pagar > 0 && response.data.remision.estado != 'Cancelado'){
                            this.remisionesData = {};
                            this.remisiones = [];
                            this.remisiones.push(response.data.remision);
                        }
                    }).catch(error => {
                        this.makeToast('danger', 'Error al consultar el numero de remisión ingresado.');
                    });
                }
            },
            // MOSTRAR CLIENTES
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
            
            ini_entregado_por(){
                this.state = null;
                this.entregado_por = null;
            },
            // REGISTRAR PAGO DE LA REMISIÓN
            registrarPago(remision, index){
                // RUTA ANTERIOR /get_remcliente
                axios.get('/cortes/show_one', {params: {cliente_id: remision.cliente_id, corte_id: remision.corte_id}}).then(response => {
                    this.deposito = {
                        remision_id: remision.id,
                        total_pagar: remision.total_pagar,
                        pagar_restante: response.data.total_pagar,
                        monto: 0,
                        entregado_por: null,
                        posicion: index
                    };

                    this.stateR = null;
                    this.stateU = null;
                    this.stateM = null;
                    this.$refs['modal-registrar-deposito'].show();
                });
            },
            // GUARDAR EL DEPOSITO DE LA RMEISION
            guardarMonto(){
                if(this.deposito.monto > 0){
                    if((this.deposito.monto <= this.deposito.pagar_restante) && (this.deposito.monto <= this.deposito.total_pagar)){
                        this.stateM = true;
                        if(this.deposito.entregado_por !== null){
                            this.stateR = true;
                            this.load = true; 
                            axios.post('/deposito_remision', this.deposito).then(response => {
                                this.load = false;
                                this.remisiones[this.deposito.posicion].pagos = response.data.pagos;
                                this.remisiones[this.deposito.posicion].total_pagar = response.data.total_pagar;
                                this.makeToast('success', 'El pago se guardo correctamente. Actualizar pagina para poder visualizar los cambios.');
                                this.$refs['modal-registrar-deposito'].hide();
                            }).catch(error => {
                                this.load = false;
                                this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                            });
                        } else {
                            this.stateR = false;
                            this.makeToast('warning', 'Seleccionar de quien se recibio el pago.');
                        }
                    } else {
                        this.stateM = false;
                        this.makeToast('warning', 'El pago no puede ser mayor al total a pagar.');
                    }
                } else{
                    this.stateM = false;
                    this.makeToast('warning', 'El pago tiene que ser mayor a 0');
                }
            },
            // MOSTRAR RESUMEN DEL PAGO PARA CONFIRMAR
            confirmarPagoU() {
                if(this.entregado_por != null){
                    this.state = true;
                    if(this.total_vendido > 0){
                        if(this.total_vendido <= this.remisiones[this.pos_remision].total_pagar){
                            this.$refs['modal-confirmarPagoU'].show();
                        } else {    
                            this.makeToast('warning', 'El pago no puede ser guardada. El total es mayor al total por pagar.');
                        }
                    } else {
                        this.makeToast('warning', 'El total debe ser mayor a cero.');
                    }
                } else {
                    this.state = false;
                    this.makeToast('warning', 'Seleccionar la opción de pago entregado por, para poder continuar.');
                }
            },
            // GUARDAR PAGO
            guardarPago () {
                this.ini_vendidos();
                axios.post('/registrar_pago', this.pagoRemision).then(response => {
                    this.remisiones[this.pos_remision].estado = response.data.estado;
                    this.remisiones[this.pos_remision].pagos = response.data.pagos;
                    this.remisiones[this.pos_remision].total_pagar = response.data.total_pagar;
                    this.$refs['modal-confirmarPagoU'].hide();
                    this.makeToast('success', 'El pago se guardo correctamente.');
                    this.mostrarPagos = false;
                    this.load = false;
                }).catch(error => {
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                    this.load = false;
                });
            },
            // REGISTRAR DEVOLUCIÓN DE LA REMISIÓN
            registrarDevolucion(remision, i){
                this.devoluciones = [];
                this.pos_remision = i;
                this.total_devolucion = 0;
                this.ini_entregado_por();
                axios.get('/remisiones/obtener_devoluciones', {params: {remisione_id: remision.id}}).then(response => {
                    // response.data.forEach(rd => {
                    //     let cs = [];
                    //     rd.dato.codes.forEach(c => {
                    //         if(!c.pivot.devolucion) cs.push(c);
                    //     });
                    //     this.devoluciones.push({
                    //         created_at: rd.created_at,
                    //         dato: rd.dato,
                    //         dato_id: rd.dato_id,
                    //         id: rd.id,
                    //         libro: rd.libro,
                    //         libro_id: rd.libro_id,
                    //         remisione_id: rd.remisione_id,
                    //         total: rd.total,
                    //         total_base: rd.total_base,
                    //         total_resta: rd.total_resta,
                    //         unidades: rd.unidades,
                    //         unidades_base: rd.unidades_base,
                    //         unidades_resta: rd.unidades_resta,
                    //         updated_at: rd.updated_at,
                    //         codes: cs,
                    //         code_dato: [],
                    //         scratch: false,
                    //         defectuosos: 0,
                    //         comentario: null
                    //     });
                    // });
                    this.devoluciones = response.data;
                    this.remision = remision;
                    this.acumularFinal();
                    this.mostrarDevolucion = true;
                }).catch(error => {
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                });
            },
            // MOSTRAR RESUMEN DE LA DEVOLUCIÓN PARA CONFIRMAR
            confirmarDevolucion(){
                if(this.entregado_por != null){
                    this.state = true;
                    if(this.total_devolucion > 0){
                        if(this.total_devolucion <= this.remisiones[this.pos_remision].total_pagar){
                            this.$refs['modal-confirmarDevolucion'].show();
                        } else {    
                            this.makeToast('warning', 'La devolución no puede ser guardada. El total de la devolución es mayor al total por pagar.');
                        }
                    } else {
                        this.makeToast('warning', 'El total debe ser mayor a cero.');
                    }
                } else{
                    this.state = false;
                    this.makeToast('warning', 'Seleccionar la opción de devolución entregada por, para poder continuar.');
                }
            },
            // GUARDAR DEVOLUCIÓN
            guardar(){
                this.load = true;
                this.devolucionRemision.id = this.remision.id;
                this.devolucionRemision.devoluciones = this.devoluciones;
                this.devolucionRemision.entregado_por = this.entregado_por;
                axios.put('/devoluciones/update', this.devolucionRemision).then(response => {
                    this.remisiones[this.pos_remision].estado = response.data.estado;
                    this.remisiones[this.pos_remision].total_devolucion = response.data.total_devolucion;
                    this.remisiones[this.pos_remision].total_pagar = response.data.total_pagar;
                    this.$refs['modal-confirmarDevolucion'].hide();
                    this.mostrarDevolucion = false;
                    this.load = false;
                    this.makeToast('success', 'La devolución se guardo correctamente. Actualizar pagina para poder visualizar los cambios.');
                }).catch(error => {
                    this.load = false;
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                });
            },
            ini_vendidos(){
                this.state = null;
                this.load = true;
                this.pagoRemision = {
                    id: this.remision.id,
                    vendidos: this.remision.vendidos,
                    entregado_por: this.entregado_por
                }
            },
            // VERIFICAR LAS UNIDADES INGRESADAS PARA OBTENER EL SUBTOTAL
            verificarUnidades (base, resta, costo, i) {
                if(base < 0){
                    this.makeToast('warning', 'Las unidades no pueden ser menores a cero.');
                    this.remision.vendidos[i].unidades_base = 0;
                    this.remision.vendidos[i].total_base = 0;
                }
                if(base > resta){
                    this.makeToast('warning', 'Las unidades son mayores a las unidades pendientes.');
                    this.remision.vendidos[i].unidades_base = 0;
                    this.remision.vendidos[i].total_base = 0;
                }
                if(base <= resta && base >= 0){
                    this.remision.vendidos[i].total_base = base * costo;
                    if(i + 1 < this.remision.vendidos.length){
                        document.getElementById('inpVend-'+(i+1)).focus();
                        document.getElementById('inpVend-'+(i+1)).select();
                    }
                }
                this.total_vendido = 0;
                this.remision.vendidos.forEach(vendido => {
                    this.total_vendido += vendido.total_base;
                });
            },
            // VERIFICAR LAS UNIDADES INGRESADAS PARA OBTENER EL SUBTOTAL
            guardarUnidades(devolucion, i){
                if (devolucion.unidades_base >= 0) {
                    if(devolucion.unidades_base <= devolucion.unidades_resta){
                        if (devolucion.unidades_base == 0) {
                            this.devoluciones[i].defectuosos = 0;
                            this.devoluciones[i].comentario = null;
                        }

                        // let total_base = devolucion.dato.costo_unitario * devolucion.unidades_base;
                        this.devoluciones[i].total_base = devolucion.dato.costo_unitario * devolucion.unidades_base;
                        this.showSelectUnit = false;
                        if (devolucion.libro.type == 'digital' && devolucion.referencia != null) {
                            let pos = this.devoluciones.findIndex(d => d.libro_id == devolucion.referencia);
                            let d = this.devoluciones[pos];
                            if (devolucion.unidades_base <= d.unidades_resta) {
                                d.unidades_base = devolucion.unidades_base;
                                d.total_base = d.dato.costo_unitario * d.unidades_base;
                            } else {
                                d.unidades_base = 0;
                                d.total_base = 0;
                                this.set_posDev(i);
                                this.showSelectUnit = true;
                                this.makeToast('warning', 'Libro físico: Unidades mayores a unidades pendientes.');
                            }
                        }
                        // if(i + 1 < this.devoluciones.length){
                        //     document.getElementById('inpDev-'+(i+1)).focus();
                        //     document.getElementById('inpDev-'+(i+1)).select();
                        // }
                    } else{
                        this.item = devolucion.id;
                        this.makeToast('warning', 'Unidades mayores a unidades pendientes.');
                        this.set_posDev(i);
                    }
                } else{
                    this.makeToast('warning', 'Las unidades no pueden ser menores a cero');
                    this.set_posDev(i);
                }
                this.acumularFinal();
            },
            set_posDev(i) {
                this.devoluciones[i].unidades_base = 0;
                this.devoluciones[i].total_base = 0;
                this.devoluciones[i].defectuosos = 0;
                this.devoluciones[i].comentario = null;
            },
            acumularFinal(){
                this.total_devolucion = 0;
                this.total_pagar = 0;
                this.devoluciones.forEach(devolucion => {
                    this.total_devolucion += devolucion.total_base;
                    this.total_pagar += devolucion.total_resta;
                });
            },
            makeToast(variant = null, descripcion) {
                this.$bvToast.toast(descripcion, {
                    title: 'Mensaje',
                    variant: variant,
                    solid: true
                })
            },
            // CERARR REMSIÓN
            cerrarRemision(remision, pos){
                this.load = true;
                let form_close = {id: remision.id};
                axios.put('/remisiones/close', form_close).then(response => {
                    this.load = false;
                    this.makeToast('success', 'La remisión se actualizo correctamente.');
                    this.remisiones.splice(pos, 1);
                }).catch(error => {
                    this.load = false;
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                });
            },
            selectCodigos(devolucion, i) {
                this.set_search(devolucion.referencia, true);
                this.assignfor_devolucion(i, false, false);
                this.codes = devolucion.codes;
                this.$bvModal.show('modal-select-codes');
            },
            selectUnidades(devolucion, i) {
                this.set_search(devolucion.referencia, false);
                this.assignfor_devolucion(i, true, true);
            },
            set_search(referencia, st) {
                if (referencia != null) {
                    let pos = this.devoluciones.findIndex(d => d.libro_id == referencia);
                    this.devoluciones[pos].status = st;
                }
            },
            assignfor_devolucion(i, ssu, scratch){
                this.position = i;
                this.devoluciones[this.position].unidades_base = 0;
                this.devoluciones[this.position].total_base = 0;
                this.devoluciones[this.position].scratch = scratch;
                this.devoluciones[this.position].code_dato = [];
                this.acumularFinal();
                this.showSelectUnit = ssu;
            },
            onRowSelected(items) {
                this.selected = items
            },
            guardarCodes() {
                this.devoluciones[this.position].code_dato = [];
                let devolucion = this.devoluciones[this.position];
                if (this.selected.length <= devolucion.unidades_resta) {
                    let unidades_base = 0;
                    this.selected.forEach(e => {
                        devolucion.code_dato.push(e.id);
                        unidades_base++;
                    });
                    devolucion.total_base = devolucion.dato.costo_unitario * unidades_base;
                    devolucion.unidades_base = unidades_base;
                    this.acumularFinal();
                    this.$bvModal.hide('modal-select-codes');
                } else {
                    this.makeToast('warning', 'El número de códigos seleccionados es mayor al número de unidades pendientes.');
                }
            },
            addDefectuosos(devolucion, i) {
                this.posD = i;
                this.devolucionD = devolucion;
                this.$bvModal.show('modal-add-defectuosos');
            },
            saveDefectuosos(form) {
                var d = parseInt(form.defectuosos);
                if ((this.devolucionD.unidades_base > 0 && this.devolucionD.unidades_base <= this.devolucionD.unidades_resta) &&
                    (d > 0 && d <= this.devolucionD.unidades_base)) {
                    this.devoluciones[this.posD].defectuosos = d;
                    this.devoluciones[this.posD].comentario = form.motivo;
                    this.$bvModal.hide('modal-add-defectuosos');
                    this.makeToast('success', 'La unidades de defectuosos se agregaron correctamente.');
                } else {
                    this.makeToast('warning', 'Las unidades para defectuosos deben ser menor o igual a las unidades de devolución.');
                }
            }
        },
    }
</script>

<style>
    #listaD{
        position: absolute;
        z-index: 100
    }
</style>