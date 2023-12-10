<template>
    <div>
        <check-connection-component></check-connection-component>
        <b-row>
            <b-col><h4 style="color: #170057">{{ !editar ? 'Crear':'Editar' }} remisión</h4></b-col>
            <b-col sm="2" align="right">
                <b-button variant="secondary" @click="goBack()">
                    <i class="fa fa-mail-reply"></i> Regresar
                </b-button>
            </b-col>
        </b-row><br>
        <!-- SELECCIONAR CLIENTE PARA UNA NUEVA REMISIÓN -->
        <div align="center" v-if="(mostrarBusqueda && !editar) || second">
            <b-row>
                <b-col sm="4"><h6 align="left"><b>Seleccionar cliente</b></h6></b-col>
                <b-col>
                    <b-row>
                        <b-col sm="2">
                            <label><i class="fa fa-search"></i> Buscar</label>
                        </b-col>
                        <b-col sm="10">
                            <b-input
                                style="text-transform:uppercase;"
                                v-model="queryCliente"
                                autofocus
                                @keyup="mostrarClientes()"></b-input>
                        </b-col>
                    </b-row>
                </b-col>
            </b-row>
            <br>
            <div v-if="clientes.length > 0">
                <!-- PAGINACIÓN -->
                <b-pagination
                    v-model="currentPage"
                    :total-rows="clientes.length"
                    :per-page="perPage"
                    aria-controls="my-table"
                    align="right"
                ></b-pagination>
                <!-- LISTADO DE CLIENTES -->
                <b-table 
                    id="my-table" 
                    :items="clientes" 
                    :fields="fieldsClientes" 
                    :per-page="perPage" 
                    :current-page="currentPage">
                    <template v-slot:cell(seleccion)="row">
                        <b-button variant="success" @click="seleccionCliente(row.item)">
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
            <b-row>
                <b-col>
                    <b-form-group label-cols="4" label-cols-lg="2" label="Periodo" label-for="input-periodo">
                        <b-form-select v-model="remision.corte_id" :options="options" 
                            required :disabled="load" autofocus id="input-periodo">
                        </b-form-select>
                    </b-form-group>
                </b-col>
                <b-col sm="3">
                    <b-form-group label-cols="4" label-cols-lg="2" label="Folio" label-for="input-folio">
                        <b-form-input v-model="remision.id" required
                            :disabled="load" id="input-folio" 
                            type="number" min="1" max="9999"
                            @change="verificarFolio()" :state="stateF">
                        </b-form-input>
                    </b-form-group>
                </b-col>
                <b-col sm="3" class="text-right">
                    <!-- GUARDAR LOS DATOS DE LA REMISIÓN -->
                    <b-button 
                        variant="success" :disabled="load"
                        @click="confirmarRemision()">
                        <i v-if="!load" class="fa fa-check"></i> 
                        <b-spinner v-else small></b-spinner>
                        {{ !editar ? !load ? 'Guardar' : 'Guardando' : !load ? 'Actualizar' : 'Actualizando' }}
                    </b-button>
                </b-col>
            </b-row>
            <hr>
            <!-- MOSTRAR DATOS DEL CLIENTE -->
            <div class="row">
                <h6 class="col-md-10"><b>Datos del cliente</b></h6>
                <div class="col-md-1">
                    <b-button 
                        id="btnEditar"
                        @click="editarInformacion()"
                        :disabled="load">
                        <i class="fa fa-pencil"></i>
                    </b-button>
                </div>
                <b-button 
                    variant="link" 
                    :class="mostrarDatos ? 'collapsed' : null"
                    :aria-expanded="mostrarDatos ? 'true' : 'false'"
                    aria-controls="collapse-1"
                    @click="mostrarDatos = !mostrarDatos">
                    <i class="fa fa-sort-asc"></i>
                </b-button>
            </div>
            <b-collapse id="collapse-1" v-model="mostrarDatos" class="mt-2">
                <div class="row">
                    <b-list-group class="col-md-6">
                        <b-list-group-item><b>Nombre:</b> {{ remision.cliente.name }}</b-list-group-item>
                        <b-list-group-item><b>Dirección:</b> {{remision.cliente.direccion  }}</b-list-group-item>
                        <b-list-group-item><b>Condiciones de pago:</b> {{ remision.cliente.condiciones_pago }}</b-list-group-item>
                    </b-list-group>
                    <b-list-group class="col-md-6">
                        <b-list-group-item><b>Correo electrónico:</b> {{ remision.cliente.email }}</b-list-group-item>
                        <b-list-group-item><b>Teléfono:</b> {{ remision.cliente.telefono }}</b-list-group-item>
                    </b-list-group>
                </div>
            </b-collapse>
            <hr>
            <div class="row">
                <div class="col-md-6">
                    <label><b>Fecha de entrega</b></label>
                    <b-form-input 
                        type="date" 
                        :state="state"
                        v-model="remision.fecha_entrega" 
                        :disabled="load">
                    </b-form-input>
                </div>
                <div class="col-md-6" align="right">
                    <label><b>Total:</b> ${{ remision.total | formatNumber }}</label>
                </div>
            </div>
            <hr>
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col" style="width: 18%;">ISBN</th>
                        <th scope="col" style="width: 37%;">Libro</th>
                        <th scope="col" style="width: 15%;">Costo unitario</th>
                        <th scope="col" style="width: 10%;">Unidades</th>
                        <th scope="col" style="width: 15%;">Subtotal</th>
                        <th scope="col" style="width: 5%;"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <b-input
                                v-model="isbn"
                                @keyup.enter="buscarLibroISBN()"
                                v-if="inputISBN"
                            ></b-input>
                            <b v-if="!inputISBN">{{ temporal.ISBN }}</b>
                        </td>
                        <td>
                            <b-input
                                style="text-transform:uppercase;"
                                v-model="queryTitulo"
                                @keyup="mostrarLibros()"
                                v-if="inputLibro"
                            ></b-input>
                            <div class="list-group" v-if="resultslibros.length" id="listaL">
                                <a 
                                    class="list-group-item list-group-item-action" 
                                    href="#" 
                                    v-bind:key="i" 
                                    v-for="(libro, i) in resultslibros" 
                                    @click="datosLibro(libro)">
                                    {{ libro.titulo }}
                                </a>
                            </div>
                            <b v-if="!inputLibro">{{ temporal.titulo }}</b>
                        </td>
                        <td>
                            <b-input 
                                type="number" 
                                autofocus
                                v-model="costo_unitario"
                                v-if="inputCosto"
                                min="1"
                                max="9999"
                                @keyup.enter="guardarCosto()">
                            </b-input>
                        </td>
                        <td>
                            <b-input 
                            autofocus
                            type="number" 
                            v-model="unidades"
                            v-if="inputUnidades"
                            min="1"
                            max="9999"
                            @keyup.enter="guardarRegistro()"
                            ></b-input>
                        </td>
                        <td></td>
                        <td>
                            <b-button 
                                variant="secondary"
                                @click="eliminarTemporal()" 
                                v-if="inputCosto || inputUnidades">
                                <i class="fa fa-minus-circle"></i>
                            </b-button>
                        </td>
                    </tr>
                    <tr v-for="(dato, i) in remision.datos" v-bind:key="i">
                        <td>{{ dato.libro.ISBN }}</td>
                        <td>{{ dato.libro.titulo }}</td>
                        <td>${{ dato.costo_unitario | formatNumber }}</td>
                        <td>{{ dato.unidades | formatNumber }}</td>
                        <td>${{ dato.total | formatNumber }}</td>
                        <td>
                            <b-button variant="danger" @click="eliminarRegistro(dato, i)">
                                <i class="fa fa-minus-circle"></i>
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
                            <b-button variant="danger" @click="eliminarRegistro(nuevo, j)">
                                <i class="fa fa-minus-circle"></i>
                            </b-button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <!-- MODAL -->
            <b-modal ref="modal-confirmar-remision" size="xl" title="Resumen de la remisión">
                <b-row>
                    <b-col><p><b>Cliente:</b> {{ remision.cliente.name }}</p></b-col>
                    <!-- <b-col sm="4">
                        <b-form-group v-if="!editar" label="Temporada">
                            <b-form-select v-model="remision.corte_id" :options="options" required
                                :disabled="load"
                            ></b-form-select>
                        </b-form-group>
                    </b-col> -->
                </b-row>
                <b-row>
                    <b-col><b>Fecha de entrega:</b> {{ remision.fecha_entrega }}</b-col>
                    <b-col align="right"><b>Total:</b> ${{ remision.total | formatNumber }}</b-col>
                </b-row>
                <hr>
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col" style="width: 18%;">ISBN</th>
                            <th scope="col" style="width: 37%;">Libro</th>
                            <th scope="col" style="width: 15%;">Costo unitario</th>
                            <th scope="col" style="width: 10%;">Unidades</th>
                            <th scope="col" style="width: 15%;">Subtotal</th>
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
                            <b-button v-if="!editar" :disabled="load || !stateF" 
                                @click="guardarRemision()" variant="success">
                                <i class="fa fa-check"></i> Confirmar
                            </b-button>
                            <b-button v-else :disabled="load" @click="actualizarRemision()" variant="success">
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
    import setCortes from '../../mixins/setCortes';
    import getLibros from '../../mixins/getLibros';
    import sweetAlert from '../../mixins/sweetAlert';
    export default {
        props: ['clientesall', 'editar', 'datoremision', 'role_id', 'cortes'],
        mixins: [setCortes, getLibros, sweetAlert],
        data() {
            return {
                load: false,
                queryCliente: '', //Buscar cliente por nombre
                resultsClientes: [], //Mostrar los resultados de la busqueda de cliente
                mostrarForm: false, //Indicar si se muestra o no la tabla, fecha y total
                isbn: '', //Buscar del libro por ISBN
                inputISBN: true, //Mostrar o no el input de ISBN
                inputLibro: true, //Mostrar o no el input de busqueda por titulo
                inputUnidades: false, //Mostrar o no el input de unidades
                inputCosto: false, //Mostrar el input de costo
                queryTitulo: '', //Buscar libro por titulo
                temporal: {}, //Guardar temporalmente los datos de la busqueda del libro
                fecha: '', //seleccionar la fecha de fecha_entrega
                bdremision: {
                    id: 0,
                    cliente_id: 0,
                    total: 0,
                    fecha_entrega: '',
                    registros: []
                }, //Para guardar todos los datos de la remision
                items: [], //Registros guardados de la remision
                unidades: null, //Asignar el numero de unidades
                costo_unitario: null, //Asignar el costo unitario
                mostrarGuardar: false, //Para mostrar el boton de guardar
                mostrarBusqueda: true, //Indicar si se muestra el apartado de buscar cliente
                mostrarOpciones: false, //Indicar si se muestran los botones de eliminar y editar
                mostrarDatos: false, //Indicar si se ocultan o muestran los datos del cliente
                clientes: this.clientesall,
                fieldsClientes: [
                    {key: 'name', label: 'Nombre'},
                    {key: 'direccion', label: 'Dirección'}, 
                    {key: 'seleccion', label: ''}
                ],
                fields: [
                    'ISBN',
                    {key: 'titulo', label: 'Libro'},
                    {key: 'costo_unitario', label: 'Costo unitario'},
                    'unidades',
                    {key: 'total', label: 'Subtotal'},
                ],
                perPage: 10,
                currentPage: 1,
                state: null,
                stateF: null,
                second: false,
                options: [],
                remision: {
                    id: null,
                    corte_id: null,
                    cliente: {},
                    fecha_entrega: '',
                    total: 0,
                    datos: [],
                    nuevos: [],
                    eliminados: []
                },
            }
        },
        created: function() {
            this.options = this.setCortes(this.cortes, null);
            if(this.editar){
                this.remision = {
                    id: this.datoremision.id,
                    corte_id: this.datoremision.corte_id,
                    cliente: this.datoremision.cliente,
                    fecha_entrega: this.datoremision.fecha_entrega,
                    total: this.datoremision.total,
                    datos: this.datoremision.datos,
                    nuevos: [],
                    eliminados: []
                };
            }
            this.ini_1();
            this.ini_2();
            this.ini_4();
        },
        filters: {
            formatNumber: function (value) {
                return numeral(value).format("0,0[.]00"); 
            }
        },
        methods: {
            // CONFIRMAR DATOS DE LA REMISIÓN
            confirmarRemision() {
                if(this.stateF){
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
                } else {
                    this.makeToast('warning', 'El folio ya esta registrado.');
                }
            },
            // GUARDAR DATOS DE REMISIÓN
            guardarRemision(){
                this.load = true;
                this.$refs['modal-confirmar-remision'].hide();
                axios.post('/remisiones/historial_store', this.remision).then(response => {
                    this.load = false;
                    this.inicializar_guardar();
                    this.messageAlert('center', 'success', 'La remisión se creó correctamente.', null, 'close');
                })
                .catch(error => {
                    this.load = false;
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                });
            },
            // ACTUALIZAR DATOS DE LA REMISION
            actualizarRemision(){
                // this.load = true;
                // this.$refs['modal-confirmar-remision'].hide();
                // axios.put('/remisiones/update', this.remision).then(response => {
                //     this.load = false;
                //     this.inicializar_guardar();
                //     mensaje de sweet
                //     .then((value) => {
                //         this.goRuta();
                //         this.goBack();
                //     });
                //     // this.$emit('actListado', response.data);
                // }).catch(error => {
                //     this.load = false;
                //     this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                // });
            },
            // MOSTRAR COINCIDENCIA DE CLIENTES
            mostrarClientes(){
                if(this.queryCliente.length > 0){
                    axios.get('/mostrarClientes', {params: {queryCliente: this.queryCliente}}).then(response => {
                        this.clientes = response.data;
                    }); 
                }
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
                this.mostrarForm = false;
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
            // BUSCAR LIBRO POR ISBN
            buscarLibroISBN(){
                axios.get('/buscarISBN', {params: {isbn: this.isbn}}).then(response => {
                    this.inicializar();
                    this.temporal = response.data;
                }).catch(error => {
                   this.makeToast('warning', 'El ISBN no existe.');
                });
            },
            // MOSTRAR LIBROS POR COINCIDENCIA
            mostrarLibros(){
                this.getLibros(this.queryTitulo);
            },
            // ASIGNAR DATOS DE LIBRO SELECCIONADO
            datosLibro(libro){
                this.inicializar();
                this.temporal = {
                    id: libro.id,
                    ISBN: libro.ISBN,
                    titulo: libro.titulo,
                    costo_unitario: null,
                    unidades: null,
                    total: 0,
                    piezas: libro.piezas
                };
            },
            // VERIFICAR EL COSTO INGRESADO
            guardarCosto(){
                if(this.costo_unitario > 0){
                    this.temporal.costo_unitario = this.costo_unitario;
                    this.inputUnidades = true;
                }
                else{
                    this.makeToast('warning', 'El costo unitario debe ser mayor a 0.');
                    
                } 
            },
            // GUARDAR REGISTRO TEMPORAL
            guardarRegistro(){
                var pzs = this.temporal.piezas;
                if(this.remision.datos.length > 0 || this.remision.nuevos.length > 0){
                    var acum = 0;
                    if(!this.editar){
                        this.remision.datos.forEach(dato => {
                            if(this.temporal.id == dato.libro.id) {
                                acum += parseInt(dato.unidades);
                                pzs = this.temporal.piezas - acum;
                            }
                        });
                    } else {
                        this.remision.nuevos.forEach(nuevo => {
                            if(this.temporal.id == nuevo.libro.id) {
                                acum += parseInt(nuevo.unidades);
                                pzs = this.temporal.piezas - acum;
                            }
                        });
                    }
                    
                }

                if(this.unidades > 0){
                    // if(this.unidades <= pzs){
                        if(this.costo_unitario > 0){
                            this.temporal.unidades = this.unidades;
                            this.temporal.total = this.unidades * this.temporal.costo_unitario;
                            var insert = {
                                libro: {
                                    id: this.temporal.id,
                                    ISBN: this.temporal.ISBN,
                                    titulo: this.temporal.titulo
                                },
                                costo_unitario: this.temporal.costo_unitario,
                                unidades: this.temporal.unidades,
                                total: this.temporal.total
                            };
                            this.mostrarDatos = false;
                            if(!this.editar){
                                this.remision.datos.push(insert);
                            } else {
                                this.remision.nuevos.push(insert);
                            }
                            this.remision.total += this.temporal.total;
                            this.inicializar_registro();
                        }
                        else{
                            this.makeToast('warning', 'El costo unitario debe ser mayor a 0');
                        } 
                    // }
                    // else{
                    //     this.makeToast('warning', `${pzs} piezas en existencia.`);
                    // }
                }
                else{
                    this.makeToast('warning', 'Las unidades deben ser mayor a 0.');
                }
            },
            // ELIMINAR REGISTRO TEMPORAL
            eliminarTemporal(){
                this.ini_1();
                this.ini_2();
                this.inputCosto = false;
                this.queryTitulo = '';
                this.costo_unitario = null;
            },
            //Cerrar la remisión (ELIMINADO)
            cancelarRemision(){
                this.ini_4();
            },
            //Inicializar los valores
            inicializar(){
                this.ini_3();
                this.resultslibros = [];
                this.costo_unitario = null;
                this.inputCosto = true;
                
            },
            //Inicializar los valores
            inicializar_registro(){
                this.ini_1();
                this.ini_2();
                this.costo_unitario = null;
                this.inputCosto = false;
                this.mostrarGuardar = true;
                this.mostrarBusqueda = false;
            },
            //Inicializar valores
            inicializar_guardar(){
                this.ini_3();
                this.temporal = {}; 
                this.inputUnidades = false;
                this.inputCosto = false;
                this.mostrarOpciones = true;
            },
            ini_1(){
                this.mostrarOpciones = false;
                this.inputLibro = true;
                this.inputISBN = true;
            }, 
            ini_2(){
                this.temporal = {};
                this.inputUnidades = false;
                this.unidades = null;
            },
            ini_3(){
                this.isbn = '';
                this.queryTitulo = '';
                this.inputISBN = false;
                this.inputLibro = false;
            },
            ini_4(){
                this.bdremision = {id: 0, cliente_id: 0, total: 0, fecha_entrega: ''};
                this.items = [];
                this.fecha = '';
                this.mostrarForm = false;
                this.mostrarGuardar = true;
                this.mostrarBusqueda = true;
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
            goRuta(){
                let ruta = '#';
                if(this.role_id == 2) ruta = '/oficina/remisiones'; // OFICINA
                if(this.role_id == 5) ruta = '/captura/remisiones'; // CAPTURA
                if(this.role_id == 6) ruta = '/manager/remisiones/lista'; // MANAGER
                window.opener.document.location=`${ruta}`;
            },
            // VERIFICAR QUE EL FOLIO NO EXISTA
            verificarFolio(){
                axios.get('/remisiones/check_folio', {params: {folio: this.remision.id}}).then(response => {
                    if(response.data.id) this.stateF = false;
                    else this.stateF = true;
                }).catch(error => { });
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
    #btnEditar {
        color: #ffb300;
        background-color: transparent;
        border: 0ch;
        font-size: 25px;
    }
    #listaL{
        position: absolute;
        z-index: 100
    }
    #columNuevos{
        color: transparent;
    }
</style>