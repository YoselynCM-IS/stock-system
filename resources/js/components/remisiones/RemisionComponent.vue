<template>
    <div>
        <check-connection-component></check-connection-component>
        <b-row>
            <b-col><h4 style="color: #170057">{{ !editar ? 'Crear':'Editar' }} remisión</h4></b-col>
            <b-col sm="2" align="right">
                <b-button variant="secondary" @click="goBack()" pill block>
                    <i class="fa fa-mail-reply"></i> Regresar
                </b-button>
            </b-col>
        </b-row><br>
        <!-- SELECCIONAR CLIENTE PARA UNA NUEVA REMISIÓN -->
        <div v-if="(mostrarBusqueda && !editar) || second">
            <b-row>
                <b-col>
                    <h6><b>Seleccionar cliente</b></h6>
                    <b-input v-model="queryCliente"
                        autofocus placeholder="Buscar cliente..."
                        style="text-transform:uppercase;"
                        @keyup="mostrarClientes()"></b-input>
                </b-col>
                <b-col>
                    <!-- PAGINACIÓN -->
                    <b-pagination v-model="currentPage" aria-controls="my-table"
                        :total-rows="clientes.length" :per-page="perPage"
                        align="right"
                    ></b-pagination>
                </b-col>
            </b-row>
            <br>
            <div v-if="clientes.length > 0">
                <!-- LISTADO DE CLIENTES -->
                <b-table :items="clientes" :fields="fieldsClientes" 
                    :per-page="perPage" :current-page="currentPage" id="my-table">
                    <template v-slot:cell(seleccion)="row">
                        <b-button variant="success" @click="seleccionCliente(row.item)" pill>
                            <i class="fa fa-check"></i>
                        </b-button>
                    </template>
                </b-table>
            </div>
            <div v-else>
                <br>
                <b-alert show variant="dark"><i class="fa fa-warning"></i> No se encontraron coincidencias</b-alert>
            </div>
        </div>
        <div v-else>
            <div class="row">
                <b-button :class="`text-left col-md-2 ${mostrarDatos ? 'collapsed' : null}`"
                    :aria-expanded="mostrarDatos ? 'true' : 'false'"
                    aria-controls="collapse-1" @click="mostrarDatos = !mostrarDatos"
                    variant="link" block pill>
                    <h6><b>Datos del cliente</b></h6>
                </b-button>
                <div class="col-md-1">
                    <b-button variant="outline-warning" pill size="sm"
                        @click="editarInformacion()" :disabled="load || (datoremision.total_devolucion > 0)">
                        <i class="fa fa-pencil"></i>
                    </b-button>
                </div>
                <div class="col-md-7"></div>
                <!-- GUARDAR LOS DATOS DE LA REMISIÓN -->
                <div class="text-right col-md-2">
                    <b-button variant="success" pill :disabled="load"
                        @click="confirmarRemision()" block>
                        <i v-if="!load" class="fa fa-check"></i> 
                        <b-spinner v-else small></b-spinner>
                        {{ !editar ? !load ? 'Guardar' : 'Guardando' : !load ? 'Actualizar' : 'Actualizando' }}
                    </b-button>
                </div>
            </div>
            <!-- MOSTRAR DATOS DEL CLIENTE -->
            <b-collapse id="collapse-1" v-model="mostrarDatos" class="mt-2">
                <div class="row">
                    <b-list-group class="col-md-6">
                        <b-list-group-item><b>Tipo:</b> {{ remision.cliente.tipo }}</b-list-group-item>
                        <b-list-group-item><b>Nombre:</b> {{ remision.cliente.name }}</b-list-group-item>
                        <b-list-group-item><b>Condiciones de pago:</b> {{ remision.cliente.condiciones_pago }}</b-list-group-item>
                    </b-list-group>
                    <b-list-group class="col-md-6">
                        <b-list-group-item><b>Dirección:</b> {{ remision.cliente.direccion }}</b-list-group-item>
                        <b-list-group-item><b>Correo electrónico:</b> {{ remision.cliente.email }}</b-list-group-item>
                        <b-list-group-item><b>Teléfono:</b> {{ remision.cliente.telefono }}</b-list-group-item>
                    </b-list-group>
                </div>
            </b-collapse>
            <hr>
            <div class="row">
                <label  class="col-md-2"><b>Fecha de entrega</b></label>
                <b-form-datepicker class="col-md-4"
                    required :disabled="load"  v-model="remision.fecha_entrega" :state="state"></b-form-datepicker>
                <div class="col-md-4"></div>
                <div class="col-md-2" align="right">
                    <b-button variant="dark" pill block @click="showScratch()">
                        Scratch
                    </b-button>
                </div>
            </div>
            <hr>
            <table class="table">
                <thead>
                    <tr>
                        <th colspan="4"></th>
                        <th><b>${{ remision.total | formatNumber }}</b></th>
                    </tr>
                    <tr>
                        <th scope="col" style="width: 18%;">ISBN</th>
                        <th scope="col" style="width: 37%;">Libro</th>
                        <th scope="col" style="width: 15%;">Costo unitario</th>
                        <th scope="col" style="width: 10%;">Unidades</th>
                        <th scope="col" style="width: 15%;">Total</th>
                        <th scope="col" style="width: 5%;"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <!-- <b-input v-model="isbn" autofocus
                                @keyup.enter="buscarLibroISBN()"
                            ></b-input> -->
                            <b>{{ temporal.ISBN }}</b>
                        </td>
                        <td>
                            <b-input style="text-transform:uppercase;"
                                v-model="temporal.titulo" autofocus
                                @keyup="mostrarLibros()"
                                :disabled="position != null"></b-input>
                            <div class="list-group" v-if="resultslibros.length" id="listaL">
                                <a class="list-group-item list-group-item-action" href="#" 
                                    v-for="(libro, i) in resultslibros" v-bind:key="i" 
                                    @click="datosLibro(libro)">
                                    {{ libro.titulo }}
                                </a>
                            </div>
                        </td>
                        <td>
                            <b-input v-model="temporal.costo_unitario"
                                type="number" max="9999">
                            </b-input>
                        </td>
                        <td>
                            <b-input type="number" v-model="temporal.unidades" min="1" max="9999"
                                    :disabled="position != null && datoremision.total_devolucion > 0" 
                            ></b-input>
                        </td>
                        <td></td>
                        <td>
                            <b-button variant="success" pill @click="guardarRegistro()"
                                size="sm">
                                <i class="fa fa-plus-circle"></i>
                            </b-button>
                        </td>
                    </tr>
                    <tr v-for="(dato, i) in remision.datos" v-bind:key="i">
                        <td>{{ dato.libro.ISBN }}</td>
                        <td>
                            {{ dato.libro.titulo }}
                            <b-badge v-if="dato.pack_id != null" variant="info">scratch</b-badge>
                        </td>
                        <td>${{ dato.costo_unitario | formatNumber }}</td>
                        <td>{{ dato.unidades | formatNumber }}</td>
                        <td>${{ dato.total | formatNumber }}</td>
                        <td v-if="!dato.scratch && dato.pack_id == null">
                            <b-button size="sm" pill variant="secondary" 
                                @click="eliminarRegistro(dato, i)"
                                :disabled="datoremision.total_devolucion > 0">
                                <i class="fa fa-minus-circle"></i>
                            </b-button>
                            <b-button v-if="editar" size="sm" pill variant="warning" 
                                @click="editarRegistro(dato, i)">
                                <i :class="`fa fa-${position == i ? 'spinner':'pencil'}`"></i>
                            </b-button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <table class="table">
                <tbody>
                    <tr v-for="(nuevo, j) in remision.nuevos" v-bind:key="j">
                        <td style="width: 18%;">{{ nuevo.libro.ISBN }}</td>
                        <td style="width: 37%;">{{ nuevo.libro.titulo }}</td>
                        <td style="width: 15%;">${{ nuevo.costo_unitario | formatNumber }}</td>
                        <td style="width: 10%;">{{ nuevo.unidades | formatNumber }}</td>
                        <td style="width: 15%;">${{ nuevo.total | formatNumber }}</td>
                        <td style="width: 5%;">
                            <b-button size="sm" pill variant="danger" @click="eliminarRegistro(nuevo, j)">
                                <i class="fa fa-minus-circle"></i>
                            </b-button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <!-- MODAL -->
            <b-modal ref="modal-confirmar-remision" size="xl" title="Resumen de la remisión">
                <b-row class="mb-3">
                    <b-col><b>Cliente:</b> {{ remision.cliente.name }}</b-col>
                    <b-col sm="4"><b>Fecha de entrega:</b> {{ remision.fecha_entrega }}</b-col>
                </b-row>
                <table class="table">
                    <thead>
                        <tr>
                            <th colspan="4"></th>
                            <th><b>${{ remision.total | formatNumber }}</b></th>
                        </tr>
                        <tr>
                            <th scope="col" style="width: 18%;">ISBN</th>
                            <th scope="col" style="width: 37%;">Libro</th>
                            <th scope="col" style="width: 15%;">Costo unitario</th>
                            <th scope="col" style="width: 10%;">Unidades</th>
                            <th scope="col" style="width: 15%;">Total</th>
                            <th scope="col" style="width: 5%;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(dato, i) in remision.datos" v-bind:key="i">
                            <td>{{ dato.libro.ISBN }}</td>
                            <td>{{ dato.libro.titulo }}</td>
                            <td>${{ dato.costo_unitario | formatNumber }}</td>
                            <td>{{ dato.unidades | formatNumber }}</td>
                            <td>${{ dato.total | formatNumber }}</td>
                        </tr>
                    </tbody>
                </table>
                <table class="table">
                    <tbody>
                        <tr v-for="(nuevo, j) in remision.nuevos" v-bind:key="j">
                            <td style="width: 18%;">{{ nuevo.libro.ISBN }}</td>
                            <td style="width: 37%;">{{ nuevo.libro.titulo }}</td>
                            <td style="width: 15%;">${{ nuevo.costo_unitario | formatNumber }}</td>
                            <td style="width: 10%;">{{ nuevo.unidades | formatNumber }}</td>
                            <td style="width: 15%;">${{ nuevo.total | formatNumber }}</td>
                        </tr>
                    </tbody>
                </table>
                <div slot="modal-footer">
                    <b-row>
                        <b-col sm="10">
                            <b-alert show variant="info">
                                <i class="fa fa-exclamation-circle"></i> <b>Verificar los datos de la remisión.</b> En caso de algún error, modificar antes de presionar <b>Confirmar</b> ya que después no se podrán realizar cambios.
                            </b-alert>
                        </b-col>
                        <b-col sm="2" align="right">
                            <b-button v-if="!editar" :disabled="load" pill block
                                @click="guardarRemision()" variant="success">
                                <i class="fa fa-check"></i> Confirmar
                            </b-button>
                            <b-button v-else :disabled="load"  pill block
                                @click="actualizarRemision()" variant="success">
                                <i class="fa fa-check"></i> Confirmar
                            </b-button>
                        </b-col>
                    </b-row>
                </div>
            </b-modal>
            <b-modal ref="modal-scratch" size="xl" title="Scratch" hide-footer>
                <table class="table mb-2">
                    <thead>
                        <tr>
                            <th style="width: 40%;">Libro</th>
                            <th>Unidades</th>
                            <th>Físico (Costo)</th>
                            <th>Digital (Costo)</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td scope="col">
                                <b-input style="text-transform:uppercase;"
                                    v-model="temporalScratch.titulo" autofocus
                                    @keyup="librosScratch()" placeholder="BUSCAR LIBRO"
                                ></b-input>
                                <div class="list-group" v-if="resultsScratch.length" id="listaL">
                                    <a class="list-group-item list-group-item-action" 
                                        href="#" v-bind:key="i" 
                                        v-for="(libro, i) in resultsScratch" 
                                        @click="selectScratch(libro)">
                                        PACK: {{ libro.titulo }}
                                    </a>
                                </div>
                            </td>
                            <td scope="col">
                                <b-input v-model="temporalScratch.unidades" type="number" min="1" max="9999"></b-input>
                            </td>
                            <td scope="col">
                                <b-input v-model="temporalScratch.costo_f" type="number" min="1" max="9999"></b-input>
                            </td>
                            <td scope="col">
                                <b-input v-model="temporalScratch.costo_d" type="number" min="1" max="9999"></b-input>
                            </td>
                            <td scope="col">
                                <b-button variant="success" pill block @click="saveScratch()">
                                    <i class="fa fa-level-down"></i>
                                </b-button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <b-table :items="packs" :fields="fieldsScratch">
                    <template v-slot:cell(index)="row">
                        {{ row.index + 1 }}
                    </template>
                    <template v-slot:cell(unidades)="row">
                        {{ row.item.unidades | formatNumber }}
                    </template>
                    <template v-slot:cell(costo_total)="row">
                        ${{ row.item.costo_total | formatNumber }}
                    </template>
                    <template v-slot:cell(total)="row">
                        ${{ row.item.total | formatNumber }}
                    </template>
                    <template v-slot:cell(actions)="row">
                        <b-button variant="danger" pill size="sm" @click="deleteScratch(row.item, row.index)">
                            <i class="fa fa-close"></i>
                        </b-button>
                        <!-- <b-button variant="warning" pill size="sm">
                            <i class="fa fa-edit"></i>
                        </b-button> -->
                    </template>
                </b-table>
            </b-modal>
        </div>
    </div>
</template>

<script>
    import getLibros from '../../mixins/getLibros';
    import sweetAlert from '../../mixins/sweetAlert';
    export default {
        props: ['clientesall', 'editar', 'datoremision', 'role_id'],
        mixins: [getLibros, sweetAlert],
        data() {
            return {
                load: false,
                queryCliente: '', //Buscar cliente por nombre
                temporal: {
                    dato_id: null,
                    id: null,
                    ISBN: null,
                    titulo: null,
                    costo_unitario: 0,
                    unidades: 0,
                    total: 0,
                    piezas: 0
                }, //Guardar temporalmente los datos de la busqueda del libro
                mostrarBusqueda: true, //Indicar si se muestra el apartado de buscar cliente
                mostrarDatos: false, //Indicar si se ocultan o muestran los datos del cliente
                clientes: this.clientesall,
                fieldsClientes: [
                    { key: 'tipo', label: 'Tipo' },
                    {key: 'name', label: 'Nombre'},
                    {key: 'email', label: 'Correo electrónico'}, 
                    {key: 'seleccion', label: 'Seleccionar'}
                ],
                perPage: 10,
                currentPage: 1,
                state: null,
                second: false,
                remision: {
                    id: null,
                    corte_id: null,
                    cliente: {},
                    fecha_entrega: '',
                    total: 0,
                    datos: [],
                    nuevos: [],
                    eliminados: [],
                    editados: []
                },
                resultsScratch: [],
                packs: [],
                temporalScratch: {
                    id: null,
                    titulo: null, 
                    libro_fisico: null,
                    libro_digital: null,
                    piezas: 0,
                    unidades: 0,
                    costo_f: 0,
                    costo_d: 0,
                    costo_total: 0,
                    total: 0
                },
                fieldsScratch: [
                    {key: 'index', label: 'N.'},
                    'titulo', 'unidades',
                    {key: 'costo_total', label: 'Costo'},
                    'total',
                    {key: 'actions', label: ''}
                ],
                position: null
            }
        },
        created: function() {
            if(this.editar){
                this.remision = {
                    id: this.datoremision.id,
                    corte_id: this.datoremision.corte_id,
                    cliente: this.datoremision.cliente,
                    fecha_entrega: this.datoremision.fecha_entrega,
                    total: this.datoremision.total,
                    datos: this.datoremision.datos,
                    nuevos: [],
                    eliminados: [],
                    editados: []
                };
            }
            this.mostrarBusqueda = true;
        },
        filters: {
            formatNumber: function (value) {
                return numeral(value).format("0,0[.]00"); 
            }
        },
        methods: {
            // CONFIRMAR DATOS DE LA REMISIÓN
            confirmarRemision() {
                if(this.remision.fecha_entrega != ''){
                    if(this.remision.datos.length > 0 || this.remision.nuevos.length > 0){
                        this.state = true;
                        this.$refs['modal-confirmar-remision'].show();
                    } else {
                        this.makeToast('warning', 'Aun no se ha agregado un libro a la remisión.');
                    }
                }
                else{
                    this.state = false;
                    this.makeToast('warning', 'Selecciona fecha de entrega');
                }
            },
            // GUARDAR DATOS DE REMISIÓN
            guardarRemision(){
                this.load = true;
                this.$refs['modal-confirmar-remision'].hide();
                axios.post('/remisiones/store', this.remision).then(response => {
                    this.load = false;
                    this.messageAlert('center', 'success', 'La remisión se creó correctamente.', '/login', 'close-opener');
                })
                .catch(error => {
                    this.load = false;
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                });
            },
            // ACTUALIZAR DATOS DE LA REMISION
            actualizarRemision(){
                this.load = true;
                // this.$refs['modal-confirmar-remision'].hide();
                axios.put('/remisiones/update', this.remision).then(response => {
                    this.load = false;
                    this.messageAlert('center', 'success', 'La remisión se actualizo correctamente.', '/login', 'close-opener');
                    // this.$emit('actListado', response.data);
                }).catch(error => {
                    this.load = false;
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                });
            },
            // MOSTRAR COINCIDENCIA DE CLIENTES
            mostrarClientes(){
                axios.get('/mostrarClientes', { params: { queryCliente: this.queryCliente } }).then(response => {
                    this.clientes = response.data;
                });
            },
            // ASIGNAR DATOS DE CLIENTE SELECCIONADO
            seleccionCliente(cliente){
                this.mostrarBusqueda = false;
                this.mostrarDatos = true;
                this.remision.cliente = cliente;
                if(this.editar)
                    this.second = false;
            },
            // INICIALIZAR PARA CAMBIAR CLIENTE
            editarInformacion(){
                this.mostrarBusqueda = true;
                this.mostrarDatos = false;
                if(this.editar)
                    this.second = true;
            },
            // ELIMINAR REGISTRO DE ARRAY
            eliminarRegistro(item, i){
                if(!this.editar){
                    this.remision.datos.splice(i, 1);
                } else {
                    if(item.id){
                        this.remision.eliminados.push(item);
                        this.remision.datos.splice(i, 1);
                    } else {
                        this.remision.nuevos.splice(i, 1);
                    }
                }
                this.remision.total = this.remision.total - item.total;
            },
            // EDITAR REGISTRO (PENDIENTE)
            editarRegistro(dato, i) {
                this.temporal = {
                    dato_id: dato.id,
                    id: dato.libro_id,
                    ISBN: dato.libro.ISBN,
                    titulo: dato.libro.titulo,
                    costo_unitario: dato.costo_unitario,
                    unidades: dato.unidades,
                    total: dato.total,
                    piezas: dato.libro.piezas
                };
                this.position = i;
            },
            // MOSTRAR LIBROS POR COINCIDENCIA
            mostrarLibros() {
                this.getLibros(this.temporal.titulo);
            },
            // ASIGNAR DATOS DE LIBRO SELECCIONADO
            datosLibro(libro){
                this.temporal = {
                    dato_id: null,
                    id: libro.id,
                    ISBN: libro.ISBN,
                    titulo: libro.titulo,
                    costo_unitario: 0,
                    unidades: 0,
                    total: 0,
                    piezas: libro.piezas
                };
                this.resultslibros = [];
            },
            // GUARDAR REGISTRO TEMPORAL
            guardarRegistro(){
                if (this.temporal.id ) {
                    var pzs = this.temporal.piezas;
                    var acum = 0;
                    var check1 = this.remision.datos.find(d => d.libro.id == this.temporal.id);
                    var check2 = this.remision.nuevos.find(d => d.libro.id == this.temporal.id);
                    if ((this.temporal.dato_id == null && check1 == undefined && check2 == undefined) || this.temporal.dato_id != null) {
                        if (this.remision.datos.length > 0 || this.remision.nuevos.length > 0) {
                            if (!this.editar) {
                                this.remision.datos.forEach(dato => {
                                    if (this.temporal.id == dato.libro.id) {
                                        acum += parseInt(dato.unidades);
                                        pzs = this.temporal.piezas - acum;
                                    }
                                });
                            } else {
                                if (this.temporal.dato_id == null) {
                                    this.remision.nuevos.forEach(nuevo => {
                                        if (this.temporal.id == nuevo.libro.id) {
                                            acum += parseInt(nuevo.unidades);
                                            pzs = this.temporal.piezas - acum;
                                        }
                                    });
                                } else {
                                    this.remision.datos.forEach(dato => {
                                        if (this.temporal.id == dato.libro.id) {
                                            acum += parseInt(dato.unidades);
                                        }
                                    });
                                    pzs = pzs + acum;
                                }
                            }
                        }

                        if (this.temporal.unidades > 0) {
                            axios.get('/libro/get_scratch', { params: { id: this.temporal.id } }).then(response => {
                                this.params_registro(pzs - response.data);
                            }).catch(error => { });
                        } else {
                            this.temporal.unidades = 0
                            this.makeToast('warning', 'Las unidades deben ser mayor a 0.');
                        }
                    } else {
                        this.makeToast('warning', 'El libro ya ha sido agregado.');
                    }
                } else {
                    this.makeToast('warning', 'Es necesario que selecciones el libro.');
                }
            },
            params_registro(pzs){
                if(this.temporal.unidades <= pzs){
                    if(this.temporal.costo_unitario >= 0){
                        this.temporal.total = this.temporal.unidades * this.temporal.costo_unitario;
                        this.mostrarDatos = false;
                        var insert = this.insert_datos(this.temporal.dato_id, this.temporal, this.temporal.costo_unitario, this.temporal.unidades, this.temporal.total, false);
                        if (!this.editar) {
                            this.remision.datos.push(insert);
                        } else {
                            if (this.temporal.dato_id == null) {
                                this.remision.nuevos.push(insert);
                            } else {
                                this.remision.datos[this.position].costo_unitario = this.temporal.costo_unitario;
                                this.remision.datos[this.position].unidades = this.temporal.unidades;
                                this.remision.datos[this.position].total = this.temporal.total;
                                var f = this.remision.editados.find(e => e == this.temporal.dato_id);
                                if (f == undefined) 
                                     this.remision.editados.push(this.remision.datos[this.position].id);
                            }
                        }
                        this.inicializar_registro();
                    } else {
                        this.temporal.costo_unitario = 0
                        this.makeToast('warning', 'El costo unitario debe ser mayor o igual a 0');
                    } 
                    this.remision.total = 0;
                    this.remision.datos.forEach(d => {
                        this.remision.total += d.total;
                    });
                    this.remision.nuevos.forEach(n => {
                        this.remision.total += n.total;
                    });
                }
                else{
                    this.makeToast('warning', `${pzs} piezas en existencia.`);
                }
            },
            //Inicializar los valores
            inicializar_registro(){
                this.temporal = {
                    dato_id: null,
                    id: null,
                    ISBN: null,
                    titulo: null,
                    costo_unitario: 0,
                    unidades: 0,
                    total: 0,
                    piezas: 0
                };
                this.position = null;
                this.resultslibros = [];
                this.mostrarBusqueda = false;
            },
            makeToast(variant = null, descripcion) {
                this.$bvToast.toast(descripcion, {
                    title: 'Mensaje',
                    variant: variant,
                    solid: true
                })
            },
            goBack(){
                window.close();
            },
            // YA NO SE UTILIZA
            goRuta(){
                let ruta = '#';
                if(this.role_id == 2) ruta = '/oficina/remisiones'; // OFICINA
                if(this.role_id == 5) ruta = '/captura/remisiones'; // CAPTURA
                if(this.role_id == 6) ruta = '/manager/remisiones/lista'; // MANAGER
                window.opener.document.location=`${ruta}`;
            },
            showScratch(){
                this.ini_temporalScratch();
                this.$refs['modal-scratch'].show();
            },
            librosScratch(){
                axios.get('/libro/all_scratch', {params: {titulo: this.temporalScratch.titulo}}).then(response => {
                    this.resultsScratch = response.data;
                }).catch(error => { });
            },
            selectScratch(libro){
                this.temporalScratch.id = libro.id;
                this.temporalScratch.libro_fisico = libro.libro_fisico;
                this.temporalScratch.libro_digital = libro.libro_digital;
                this.temporalScratch.piezas = libro.piezas;
                this.temporalScratch.titulo = `PACK: ${libro.titulo}`;
                this.resultsScratch = [];
            },
            saveScratch(){
                var check_f = this.remision.datos.find(d => d.libro.id == this.temporalScratch.libro_fisico);
                var check_d = this.remision.datos.find(d => d.libro.id == this.temporalScratch.libro_digital);
                var check = this.packs.find(p => p.id == this.temporalScratch.id);
                if(check == undefined && check_f == undefined && check_d == undefined){
                    if(this.temporalScratch.unidades > 0){
                        if(this.temporalScratch.unidades <= this.temporalScratch.piezas){
                            if(this.temporalScratch.costo_f > 0 && this.temporalScratch.costo_d > 0){
                                axios.get('/libro/scratch_libros', {params: {
                                    f: this.temporalScratch.libro_fisico, d: this.temporalScratch.libro_digital}})
                                .then(response => {
                                    this.temporalScratch.costo_total = parseFloat(this.temporalScratch.costo_f) + parseFloat(this.temporalScratch.costo_d);
                                    this.temporalScratch.total = this.temporalScratch.unidades * this.temporalScratch.costo_total;

                                    this.packs.push(this.temporalScratch);

                                    response.data.forEach(r => {
                                        var costo_unitario = 0;
                                        if(r.type == 'venta') costo_unitario = this.temporalScratch.costo_f;
                                        if(r.type == 'digital') costo_unitario = this.temporalScratch.costo_d;

                                        var total = this.temporalScratch.unidades * costo_unitario;
                                        this.remision.datos.push(this.insert_datos(null, r, costo_unitario, this.temporalScratch.unidades, total, true));
                                    });

                                    this.remision.total += this.temporalScratch.total;
                                    this.ini_temporalScratch();
                                }).catch(error => { });
                            } else{
                                this.makeToast('warning', 'El costo debe ser mayor a 0');
                            } 
                        } else {
                            this.makeToast('warning', `${this.temporalScratch.piezas} piezas en existencia.`);
                        }
                    } else {
                        this.makeToast('warning', 'Las unidades deben ser mayor a 0.');
                    }
                } else{
                    this.makeToast('warning', 'El libro ya ha sido agregado.');
                }
            },
            insert_datos(dato_id, libro, cu, u, t, scratch){
                return {
                    dato_id: dato_id,
                    libro: {
                        id: libro.id,
                        ISBN: libro.ISBN,
                        titulo: libro.titulo
                    },
                    costo_unitario: cu,
                    unidades: u,
                    total: t,
                    scratch: scratch
                };
            },
            ini_temporalScratch(){
                this.temporalScratch = {
                    id: null,
                    titulo: null,
                    libro_fisico: null,
                    libro_digital: null,
                    piezas: 0,
                    unidades: 0,
                    costo_f: 0,
                    costo_d: 0,
                    costo_total: 0,
                    total: 0
                };
                this.resultsScratch = [];
            },
            deleteScratch(libro, position){
                let positions = [];
                this.remision.datos.find(function(value, index) {
                    if(value.libro.id == libro.libro_fisico) positions.push(index);
                    if(value.libro.id == libro.libro_digital) positions.push(index);
                });

                positions.reverse()
                positions.forEach(p => {
                    this.remision.datos.splice(p, 1);
                });

                this.packs.splice(position, 1);

                this.remision.total = this.remision.total - libro.total;
            }
        }
    }
</script>

<style> 
    #listaL{
        position: absolute;
        z-index: 100
    }
</style>