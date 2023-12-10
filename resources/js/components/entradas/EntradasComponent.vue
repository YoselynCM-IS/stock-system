<template>
    <div>
        <check-connection-component></check-connection-component>
        <div v-if="listadoEntradas">
            <b-row>
                <b-col>
                     <!-- BUSCAR ENTRADA POR FOLIO -->
                    <b-row>
                        <b-col sm="2" class="text-right"><label>Folio</label></b-col>
                        <b-col sm="10">
                            <b-form-input v-model="folio" @keyup.enter="porFolio()" style="text-transform:uppercase;"></b-form-input>
                        </b-col>
                    </b-row>
                </b-col>
                <!-- MOSTRAR ENTRADAS POR EDITORIAL -->
                <b-col>
                    <b-row>
                        <b-col sm="3" class="text-right"><label>Editorial</label></b-col>
                        <b-col sm="9">
                            <b-form-select v-model="editorial" :options="options" @change="mostrarEditoriales()"></b-form-select>
                        </b-col> 
                    </b-row>
                </b-col>
                <!-- MOSTRAR ENTRADAS POR FECHA -->
                <b-col>
                    <b-row>
                        <b-col sm="1" class="text-right"><label>De:</label></b-col>
                        <b-col sm="10">
                            <b-input type="date" v-model="inicio" @change="porFecha()" :state="stateDate"></b-input>
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col sm="1" class="text-right"><label>A:</label></b-col>
                        <b-col sm="10">
                            <b-input type="date" v-model="final" @change="porFecha()"></b-input>
                        </b-col>
                    </b-row>
                </b-col>
            </b-row>
            <br>
            <b-row>
                <b-col sm="5">
                    <!-- PAGINACIÓN -->
                    <b-pagination
                        v-model="currentPage"
                        :total-rows="entradas.length"
                        :per-page="perPage"
                        aria-controls="my-table"
                        v-if="entradas.length > 0"
                    ></b-pagination>
                </b-col>
                <!-- <b-col sm="2">
                    <b-button variant="info" pill v-b-modal.modal-ayudaAE><i class="fa fa-info-circle"></i> Ayuda</b-button>
                </b-col> -->
                <b-col class="text-right">
                    <b-button v-if="entradas.length > 0" variant="dark" :href="`/downEntradas/${inicio}/${final}/${editorial}`">
                        <i class="fa fa-download"></i> PDF
                    </b-button>
                </b-col>
                <b-col sm="3" class="text-right">
                    <b-button variant="success" @click="nuevaEntrada()"><i class="fa fa-plus"></i> Nueva entrada</b-button>
                </b-col>
            </b-row>
            <!-- LISTADO DE ENTRADAS -->
            <b-table 
                responsive
                v-if="entradas.length > 0" 
                :items="entradas" 
                :fields="fields"
                id="my-table" 
                :tbody-tr-class="rowClass"
                :per-page="perPage" 
                :current-page="currentPage">
                <template v-slot:cell(created_at)="row">
                    {{ row.item.created_at | moment }}
                </template>
                <template v-slot:cell(detalles)="row">
                    <b-button variant="info" @click="detallesEntrada(row.item)">Detalles</b-button>
                </template>
                <template v-slot:cell(editar)="row">
                    <b-button 
                        style="color:white;"
                        v-if="row.item.total === 0"
                        @click="editarEntrada(row.item, row.index)"
                        variant="warning">
                        <i class="fa fa-pencil"></i>
                    </b-button>
                    <b-button
                        v-if="row.item.total > 0 && ((row.item.total - (row.item.total_pagos + row.item.total_devolucion)) > 0)"
                        @click="registrarDevolucion(row.item, row.index)"
                        variant="primary">Devolución
                    </b-button>
                </template>
                <template v-slot:cell(unidades)="row">{{ row.item.unidades | formatNumber }}</template>
                <template v-slot:cell(total)="row">${{ row.item.total | formatNumber }}</template>
                <template v-slot:cell(total_pagos)="row">${{ row.item.total_pagos | formatNumber }}</template>
                <template v-slot:cell(total_devolucion)="row">${{ row.item.total_devolucion | formatNumber }}</template>
                <template v-slot:cell(total_pendiente)="row">
                    ${{ (row.item.total - (row.item.total_pagos + row.item.total_devolucion)) | formatNumber }}
                </template>
                
                <template #thead-top="row">
                    <tr>
                        <th colspan="3"></th>
                        <th>{{ total | formatNumber }}</th>
                        <th colspan="4"></th>
                    </tr>
                </template>
            </b-table>
            <div v-else>
                <br>
                <b-alert show variant="secondary">
                    <i class="fa fa-warning"></i> No se encontraron registros.
                </b-alert>
            </div>
        </div>
        <!-- MOSTRAR DETALLES DE LA ENTRADA -->
        <div v-if="mostrarDetalles">
            <b-row>
                <b-col sm="6">
                    <label><b>Folio:</b> {{entrada.folio}}</label><br>
                    <label><b>Fecha de creación:</b> {{entrada.created_at | moment}}</label>
                </b-col>
                <b-col sm="4">
                    <b-button variant="dark" :href="'/downloadEntrada/' + entrada.id">
                        <i class="fa fa-download"></i> Descargar
                    </b-button>
                </b-col>
                <b-col sm="2" align="right">
                    <b-button 
                        variant="secondary" 
                        @click="mostrarDetalles = false; listadoEntradas = true;">
                        <i class="fa fa-mail-reply"></i> Regresar
                    </b-button>
                </b-col>
            </b-row>
            <label><b>Editorial:</b> {{entrada.editorial}}</label>
            <b-table v-if="registros.length > 0" :items="registros" :fields="fieldsDet">
                <template v-slot:cell(index)="row">{{ row.index + 1}}</template>
                <template v-slot:cell(ISBN)="row">{{ row.item.libro.ISBN }}</template>
                <template v-slot:cell(titulo)="row">{{ row.item.libro.titulo }}</template>
                <template v-slot:cell(costo_unitario)="row">${{ row.item.costo_unitario | formatNumber }}</template>
                <template v-slot:cell(total)="row">${{ row.item.total | formatNumber }}</template>
                <template v-slot:cell(unidades)="row">{{ row.item.unidades | formatNumber }}</template>
                <template #thead-top="row">
                    <tr>
                        <th colspan="4"></th>
                        <th>{{ entrada.unidades | formatNumber }}</th>
                        <th>${{ entrada.total | formatNumber }}</th>
                    </tr>
                </template>
            </b-table>
            <div v-if="entdevoluciones.length > 0">
                <hr><br>
                <h5>Devolución</h5>
                <b-table :items="entdevoluciones" :fields="fieldsED">
                    <template v-slot:cell(index)="row">{{ row.index + 1}}</template>
                    <template v-slot:cell(ISBN)="row">{{ row.item.registro.libro.ISBN }}</template>
                    <template v-slot:cell(titulo)="row">{{ row.item.registro.libro.titulo }}</template>
                    <template v-slot:cell(costo_unitario)="row">${{ row.item.registro.costo_unitario | formatNumber }}</template>
                    <template v-slot:cell(total)="row">${{ row.item.total | formatNumber }}</template>
                    <template v-slot:cell(unidades)="row">{{ row.item.unidades | formatNumber }}</template>
                    <template v-slot:cell(created_at)="row">{{ row.item.created_at | moment }}</template>
                    <template #thead-top="row">
                    <tr>
                        <th colspan="6"></th>
                        <th>${{ entrada.total_devolucion | formatNumber }}</th>
                    </tr>
                </template>
                </b-table>
            </div>
        </div>
        <!-- REGISTRAR DEVOLUCIÓN -->
        <div v-if="mostrarDevolucion">
            <b-row>
                <b-col sm="8">
                    <h4 style="color: #170057">Registrar devolución</h4>
                    <label><b>Folio:</b> {{entrada.folio}}</label><br>
                    <label><b>Editorial:</b> {{entrada.editorial}}</label>
                </b-col>
                <b-col>
                    <b-button variant="success" @click="confirmarDevolucion()"><i class="fa fa-check"></i> Guardar</b-button>
                </b-col>
                <b-col align="right">
                    <b-button 
                        variant="secondary" 
                        @click="mostrarDevolucion = false; listadoEntradas = true;">
                        <i class="fa fa-mail-reply"></i> Regresar
                    </b-button>
                </b-col>
            </b-row>
            <b-table v-if="registros.length > 0" :items="registros" :fields="fieldsD">
                <template v-slot:cell(index)="row">{{ row.index + 1}}</template>
                <template v-slot:cell(ISBN)="row">{{ row.item.libro.ISBN }}</template>
                <template v-slot:cell(titulo)="row">{{ row.item.libro.titulo }}</template>
                <template v-slot:cell(costo_unitario)="row">${{ row.item.costo_unitario | formatNumber }}</template>
                <template v-slot:cell(total_base)="row">${{ row.item.total_base | formatNumber }}</template>
                <template v-slot:cell(unidades_base)="row">
                    <b-input 
                        :id="`inpEntDev-${row.index}`"
                        type="number" 
                        @change="obtenerSubtotal(row.item, row.index)"
                        v-model="row.item.unidades_base">
                    </b-input>
                </template>
                <template #thead-top="row">
                    <tr>
                        <th colspan="4"></th>
                        <th>{{ todo_unidades | formatNumber }}</th>
                        <th>${{ todo_total | formatNumber }}</th>
                    </tr>
                </template>
            </b-table>
            <b-modal ref="modal-confirmarDevolucion" size="xl" title="Resumen de la devolución">
                <label><b>Folio:</b> {{entrada.folio}}</label><br>
                <label><b>Editorial:</b> {{entrada.editorial}}</label><br>
                <b-table :items="registros" :fields="fieldsD">
                    <template v-slot:cell(index)="row">{{ row.index + 1}}</template>
                    <template v-slot:cell(ISBN)="row">{{ row.item.libro.ISBN }}</template>
                    <template v-slot:cell(titulo)="row">{{ row.item.libro.titulo }}</template>
                    <template v-slot:cell(costo_unitario)="row">${{ row.item.costo_unitario | formatNumber }}</template>
                    <template v-slot:cell(total_base)="row">${{ row.item.total_base | formatNumber }}</template>
                    <template v-slot:cell(unidades_base)="row">{{ row.item.unidades_base | formatNumber }}</template>
                    <template #thead-top="row">
                        <tr>
                            <th colspan="4"></th>
                            <th>{{ todo_unidades | formatNumber }}</th>
                            <th>{{ todo_total | formatNumber }}</th>
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
                                @click="guardarDevolucion()">
                                <i class="fa fa-check"></i> Confirmar
                            </b-button>
                        </b-col>
                    </b-row>
                </div>
            </b-modal>
        </div>
        <!-- CREAR / EDITAR ENTRADA -->
        <div v-if="mostrarEA">
            <b-row>
                <b-col sm="3">
                    <h4 style="color: #170057">{{ agregar ? 'Nueva' : 'Editar' }} entrada</h4>
                </b-col>
                <b-col sm="6" class="text-right">
                    <b-button @click="confirmarEntrada()" variant="success" :disabled="load">
                        <i v-if="agregar === true" class="fa fa-check"> {{ !load ? 'Guardar' : 'Guardando' }}</i>
                        <i v-else class="fa fa-check"> {{ !load ? 'Guardar  cambios' : 'Guardando' }}</i>
                    </b-button>
                </b-col>
                <b-col sm="3" class="text-right">
                    <b-button variant="secondary" @click="mostrarEA = false; listadoEntradas = true;"><i class="fa fa-mail-reply"></i> Regresar</b-button>
                </b-col>
            </b-row>
            <hr>
            <b-row>
                <b-col sm="1"><label>Folio</label></b-col>
                <b-col sm="5">
                    <b-form-input
                        style="text-transform:uppercase;"
                        v-model="entrada.folio"
                        autofocus
                        :disabled="load"
                        :state="stateN"
                        @change="guardarNum()">
                    </b-form-input>
                </b-col>
                <b-col sm="3" align="right">
                    <label><b>Unidades:</b> {{ total_unidades | formatNumber }}</label>
                </b-col>
            </b-row>
            <b-row>
                <b-col sm="1"><label>Editorial</label></b-col>
                <b-col sm="5">
                    <b-form-select
                        v-model="entrada.editorial"
                        autofocus
                        :state="stateE"
                        :options="optionsEdit">
                    </b-form-select>
                </b-col>
            </b-row>
            <hr>
            <b-table :items="registros" :fields="fieldsRE">
                <template v-slot:cell(index)="row">{{ row.index + 1}}</template>
                <template v-slot:cell(ISBN)="row">{{ row.item.libro.ISBN }}</template>
                <template v-slot:cell(titulo)="row">{{ row.item.libro.titulo }}</template>
                <template v-slot:cell(unidades)="row">{{ row.item.unidades | formatNumber }}</template>
                <template v-slot:cell(eliminar)="row">
                    <b-button v-if="registros.length > 0 && agregar == true" variant="danger" @click="eliminarRegistro(row.item, row.index)">
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
                                autofocus
                                :disabled="entrada.editorial === null"
                                v-model="isbn"
                                @keyup.enter="buscarLibroISBN()"
                                v-if="inputISBN"
                            ></b-input>
                            <label v-if="!inputISBN">{{ temporal.libro.ISBN }}</label>
                        </th>
                        <th>
                            <b-input
                                autofocus
                                :disabled="entrada.editorial === null"
                                style="text-transform:uppercase;"
                                v-model="queryTitulo"
                                @keyup="mostrarLibros()"
                                v-if="inputLibro">
                            </b-input>
                            <div class="list-group" v-if="resultslibros.length" id="listaLR">
                                <a 
                                    href="#" 
                                    v-bind:key="i" 
                                    class="list-group-item list-group-item-action" 
                                    v-for="(libro, i) in resultslibros" 
                                    @click="datosLibro(libro)">
                                    {{ libro.titulo }}
                                </a>
                            </div>
                            <label v-if="!inputLibro">{{ temporal.libro.titulo }}</label>
                        </th>
                        <th>
                            <b-form-input 
                                autofocus
                                @keyup.enter="guardarRegistro()"
                                v-if="inputUnidades"
                                v-model="temporal.unidades" 
                                type="number"
                                required>
                            </b-form-input>
                        </th>
                        <th>
                            <b-button 
                                variant="secondary"
                                @click="eliminarTemporal()" 
                                v-if="inputUnidades">
                                <i class="fa fa-minus-circle"></i>
                            </b-button>
                        </th>
                    </tr>
                </template>
            </b-table>
            <!-- MODALS -->
            <b-modal ref="modal-confirmarEntrada" size="xl" title="Resumen de la entrada">
                <b-row>
                    <b-col sm="8">
                        <label><b>Folio:</b> {{entrada.folio}}</label><br>
                        <label><b>Editorial:</b> {{entrada.editorial}}</label>
                    </b-col>
                    <b-col sm="4"><label><b>Unidades:</b> {{ total_unidades | formatNumber }}</label></b-col>
                </b-row>
                <b-table :items="registros" :fields="fieldsRE">
                    <template v-slot:cell(index)="row">{{ row.index + 1}}</template>
                    <template v-slot:cell(ISBN)="row">{{ row.item.libro.ISBN }}</template>
                    <template v-slot:cell(titulo)="row">{{ row.item.libro.titulo }}</template>
                    <template v-slot:cell(costo_unitario)="row">${{ row.item.costo_unitario | formatNumber }}</template>
                    <template v-slot:cell(total)="row">${{ row.item.total | formatNumber }}</template>
                    <template v-slot:cell(unidades)="row">{{ row.item.unidades | formatNumber }}</template>
                </b-table>
                <div slot="modal-footer">
                    <b-row>
                        <b-col sm="10">
                            <b-alert show variant="info">
                                <i class="fa fa-exclamation-circle"></i> <b>Verificar los datos de la entrada.</b> En caso de algún error, modificar antes de presionar <b>Confirmar</b> ya que después no se podrán realizar cambios.
                            </b-alert>
                        </b-col>
                        <b-col sm="2" align="right">
                            <b-button 
                                @click="actEntrada()" 
                                variant="success"
                                :disabled="load"
                                v-if="agregar == false">
                                <i class="fa fa-check"></i> Confirmar
                            </b-button>
                            <b-button 
                                @click="onSubmit()" 
                                variant="success"
                                :disabled="load"
                                v-if="agregar === true">
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
    export default {
        props: ['registersall', 'editoriales'],
        data() {
            return {
                entradas: this.registersall,
                todo_unidades: 0,
                todo_total: 0,
                registros: [],
                editorial: 'TODAS',
                options: [],
                optionsEdit: [],
                fields: [
                    'folio',
                    {key: 'created_at', label: 'Fecha'},
                    'editorial', 'unidades', 'total',
                    // {key: 'total_pagos', label: 'Pagos'},
                    {key: 'total_devolucion', label: 'Devolución'},
                    {key: 'total_pendiente', label: 'Pagar'},
                    {key: 'detalles', label: ''},
                    {key: 'editar', label: ''}
                ],
                fieldsRE: [
                    {key: 'index', label: 'N.'}, 
                    {key: 'ISBN', label: 'ISBN'}, 
                    {key: 'titulo', label: 'Libro'}, 
                    'unidades', 
                    {key: 'eliminar', label: ''}
                ],
                fieldsDet: [
                    {key: 'index', label: 'N.'}, 
                    {key: 'ISBN', label: 'ISBN'}, 
                    {key: 'titulo', label: 'Libro'}, 
                    {key: 'costo_unitario', label: 'Costo unitario'},
                    'unidades',
                    {key: 'total', label: 'Subtotal'},
                ],
                fieldsD: [
                    {key: 'index', label: 'N.'}, 
                    {key: 'ISBN', label: 'ISBN'}, 
                    {key: 'titulo', label: 'Libro'}, 
                    {key: 'costo_unitario', label: 'Costo unitario'}, 
                    {key: 'unidades_base', label: 'Unidades'}, 
                    {key: 'total_base', label: 'Subtotal'}
                ],
                fieldsED: [
                    {key: 'index', label: 'N.'}, 
                    {key: 'created_at', label: 'Fecha'},
                    {key: 'ISBN', label: 'ISBN'}, 
                    {key: 'titulo', label: 'Libro'}, 
                    {key: 'costo_unitario', label: 'Costo unitario'}, 
                    {key: 'unidades', label: 'Unidades'}, 
                    {key: 'total', label: 'Subtotal'}
                ],
                mostrarDetalles: false,
                mostrarDevolucion: false,
                entrada: {
                    id: 0,
                    unidades: 0,
                    total: 0,
                    folio: '',
                    editorial: null,
                    items: [],
                    nuevos: [],
                    total_devolucion: 0,
                    created_at: null
                },
                mostrarEA: false,
                isbn: '',
                inputISBN: true,
                temporal: {},
                queryTitulo: '',
                inputLibro: true,
                resultslibros: [],
                inputUnidades: false,
                unidades: null,
                total_unidades: 0,
                stateN: null,
                stateE: null,
                load: false,
                posicion: null,
                listadoEntradas: true,
                agregar: false,
                nuevos: [],
                total: 0,
                estado: false,
                folio: '',
                perPage: 10,
                currentPage: 1,
                loadRegisters: false,
                inicio: '0000-00-00',
                final: '0000-00-00',
                stateDate: null,
                datoEditorial: null,
                entdevoluciones: []
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
            this.assign_editorial();
            this.acumular();
        },
        methods: {
            rowClass(item, type) {
                if (!item) return
                if (item.total_pagos > 0 && item.total_pagos == item.total) return 'table-success'
                if (item.total == 0) return 'table-warning'
            },
            assign_editorial(){
                this.options.push({
                    value: 'TODAS',
                    text: 'MOSTRAR TODO'
                });
                this.editoriales.forEach(editorial => {
                    this.options.push({
                        value: editorial.editorial,
                        text: editorial.editorial
                    });
                });

                this.optionsEdit.push({
                    value: null,
                    text: 'Selecciona una opción',
                    disabled: true
                });
                this.editoriales.forEach(editorial => {
                    this.optionsEdit.push({
                        value: editorial.editorial,
                        text: editorial.editorial
                    });
                });
            },
            // BUSCAR ENTRADA POR FOLIO
            porFolio(){
                axios.get('/buscarFolio', {params: {folio: this.folio}}).then(response => {
                    if(response.data.id != undefined){
                        this.entradas = [];
                        this.entradas.push(response.data);
                        this.acumular();
                    }
                    else{
                        this.makeToast('warning', 'El folio no existe');
                    }
                }).catch(error => {
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                });
            },
            // BUSCAR ENTRADAS POR EDITORIAL
            mostrarEditoriales(){
                axios.get('/mostrarEditoriales', {params: {editorial: this.editorial}}).then(response => {
                    this.entradas = response.data;
                    this.acumular();
                    this.inicio = '0000-00-00';
                    this.final = '0000-00-00';
                    this.folio = '';
                }).catch(error => {
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                });
            },
            // MOSTRAR ENTRADAS POR FECHA
            porFecha(){
                if(this.final != '0000-00-00'){
                    if(this.inicio != '0000-00-00') {
                        this.stateDate = null;
                        axios.get('/fecha_entradas', {params: {inicio: this.inicio, final: this.final, editorial: this.editorial}}).then(response => {
                            this.entradas = response.data;
                            this.acumular();
                            this.folio = '';
                        }).catch(error => {
                            this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                        });
                    } else {
                        this.stateDate = false;
                        this.makeToast('warning', 'Es necesario seleccionar la fecha de inicio');
                    }
                }
            },
            // INICIALIZAR PARA CREAR UNA ENTRADA
            nuevaEntrada(){
                this.listadoEntradas = false;
                this.agregar = true;
                this.mostrarEA = true;
                this.entrada = {
                    id: 0,
                    unidades: 0,
                    total: 0,
                    folio: '',
                    editorial: null,
                    items: [],
                    nuevos: [],
                    total_devolucion: 0
                };
                this.registros = [];
                this.eliminarTemporal();
                this.total_unidades = 0;
                this.stateN = null;
                this.stateE = null;
                this.resultslibros = [];
            },
            // OBTENER LOS DETALLES DE LA ENTRADA
            detallesEntrada(entrada){
                axios.get('/detalles_entrada', {params: {entrada_id: entrada.id}}).then(response => {
                    this.assign_datos(response);
                    this.listadoEntradas = false;
                    this.mostrarDetalles = true;
                });
            },
            // EDITAR ENTRADA
            editarEntrada(entrada, i){
                this.posicion = i;
                this.agregar = false;
                this.stateN = true;
                this.stateE = true;
                this.nuevos = [];
                this.entrada.nuevos = [];
                this.resultslibros = [];
                axios.get('/detalles_entrada', {params: {entrada_id: entrada.id}}).then(response => {
                    this.eliminarTemporal();
                    this.listadoEntradas = false;
                    this.mostrarEA = true;
                    this.assign_datos(response);
                    this.total_unidades = this.entrada.unidades;
                });
            },
            assign_datos(response){
                this.entrada.id = response.data.entrada.id;
                this.entrada.folio = response.data.entrada.folio;
                this.entrada.editorial = response.data.entrada.editorial;
                this.entrada.total = response.data.entrada.total;
                this.entrada.unidades = response.data.entrada.unidades;
                this.entrada.total_devolucion = response.data.entrada.total_devolucion;
                this.entrada.created_at = response.data.entrada.created_at;
                this.registros = response.data.entrada.registros;
                this.entdevoluciones = response.data.entdevoluciones;
            },
            // ACTUALIZAR DATOS DE ENTRADA
            actEntrada(){
                if(this.stateN !== false){
                    this.entrada.unidades = this.total_unidades;
                    this.entrada.nuevos = this.nuevos;
                    this.load = true;
                    axios.put('/entradas/update', this.entrada).then(response => {
                        this.makeToast('success', 'La entrada se ha actualizado');
                        this.load = false;
                        this.entradas[this.posicion].folio = response.data.folio;
                        this.entradas[this.posicion].editorial = response.data.editorial;
                        this.entradas[this.posicion].unidades = response.data.unidades;
                        this.entradas[this.posicion].total = response.data.total;
                        this.acumular();
                        this.mostrarEA = false;
                        this.listadoEntradas = true;
                    }).catch(error => {
                        this.load = false;
                        this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                    });
                }
                else{
                    this.stateN = false;
                    this.makeToast('warning', 'El folio ya existe, favor de corregirlo.');
                }
            },
            // CONFIRMAR PARA GUARDAR LA ENTRADA
            confirmarEntrada(){
                if(this.entrada.folio.length > 0 && this.stateN !== false){
                    this.stateN = true;
                    this.stateE = true;
                    if(this.registros.length > 0){
                        this.$refs['modal-confirmarEntrada'].show();
                    } else {
                        this.makeToast('warning', 'Agregar registros.');
                    }
                }
                else{
                    this.stateN = false;
                    this.makeToast('warning', 'Verificar el folio.');
                }
            },
            // GUARDAR DATOS DE ENTRADA
            onSubmit(){
                if(this.stateN !== false){
                    this.load = true;
                    this.entrada.unidades = this.total_unidades;
                    this.entrada.items = this.registros;
                    axios.post('/entradas/store', this.entrada).then(response => {
                        this.entradas.unshift(response.data);
                        this.acumular();
                        this.load = false;
                        this.mostrarEA = false;
                        this.listadoEntradas = true;
                        this.makeToast('success', 'La entrada se creo correctamente');
                    }).catch(error => {
                        this.load = false;
                        this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                    });
                } else{
                    this.stateN = false;
                    this.makeToast('warning', 'El folio ya existe, favor de corregirlo.');
                }
            },
            // VERIFICAR QUE EL FOLIO NO EXISTA
            guardarNum(){
                if(this.entrada.folio.length > 0){
                    axios.get('/buscarFolio', {params: {folio: this.entrada.folio}}).then(response => {
                        if(response.data.id != undefined){
                            this.stateN = false;
                            this.makeToast('warning', 'El folio ya existe.');
                        }
                        else{
                            this.stateN = true;
                        }
                    }).catch(error => {
                        this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                    });
                }
                else{
                    this.stateN = false;
                    this.makeToast('warning', 'Definir folio.');
                }
            },
            // ELIMINAR REGISTRO DE ENTRADA
            eliminarRegistro(item, i){
                this.restasUnidades(item, i);
            },
            restasUnidades(item, i){
                this.registros.splice(i, 1);
                this.total_unidades = this.total_unidades - item.unidades;
                this.entrada.unidades = this.total_unidades;
            },
            // BUSCAR LIBRO POR ISBN
            buscarLibroISBN(){
                this.inicializar_editorial();
                axios.get('/isbn_por_editorial', {params: {isbn: this.isbn, editorial: this.datoEditorial}}).then(response => {
                    this.datosLibro(response.data);
                }).catch(error => {
                    this.makeToast('warning', 'ISBN es incorrecto o pertenece a otra editorial.');
                });
            }, 
            // MOSTRAR COINCIDENCIAS DE LIBRO
            mostrarLibros(){
                if(this.queryTitulo.length > 0){
                    this.inicializar_editorial();
                    axios.get('/libros_por_editorial', {params: {queryTitulo: this.queryTitulo, editorial: this.datoEditorial}}).then(response => {
                        this.resultslibros = response.data;
                    }).catch(error => {
                        this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                    });
                } else {
                    this.resultslibros = [];
                }      
            },
            inicializar_editorial(){
                this.datoEditorial = this.entrada.editorial;
                if(this.agregar == false && this.entrada.folio === '05' && this.entrada.editorial === 'MAJESTIC'){
                    this.datoEditorial = '';
                }
            },
            // ASIGNAR DATOS DE LIBRO SELECCIONADO
            datosLibro(libro){
                this.ini_1();
                this.temporal = {
                    id: libro.id,
                    libro: {
                        ISBN: libro.ISBN,
                        titulo: libro.titulo,
                    },
                    unidades: null,
                };
            },
            // OBTENER SUBTOTAL
            obtenerSubtotal(registro, i){
                if(registro.unidades_base <= registro.libro.piezas){
                    if(registro.unidades_base >= 0){
                        if(registro.unidades_base > registro.unidades_pendientes){
                            this.makeToast('warning', 'Las unidades son mayor a las unidades pendientes');
                            this.to_zero(i);
                        }
                        else{
                            this.registros[i].total_base = registro.unidades_base * registro.costo_unitario;
                            if(i + 1 < this.registros.length){
                                document.getElementById('inpEntDev-'+(i+1)).focus();
                                document.getElementById('inpEntDev-'+(i+1)).select();
                            }
                        }
                    }
                    else{
                        this.makeToast('warning', 'Unidades invalidas');
                        this.to_zero(i);
                    } 
                } else {
                    this.makeToast('warning', `Hay ${registro.libro.piezas} en existencia`);
                    this.to_zero(i);
                }
                this.todo_unidades = 0;
                this.todo_total = 0;
                this.registros.forEach(registro => {
                    this.todo_unidades += parseInt(registro.unidades_base);
                    this.todo_total += parseFloat(registro.total_base);
                });
            },
            to_zero(i){
                this.registros[i].unidades_base = 0;
                this.registros[i].total_base = 0;
            },
            // REGISTRAR ENTRADA
            registrarDevolucion(entrada, i){
                this.todo_total = 0;
                this.todo_unidades = 0;
                axios.get('/detalles_entrada', {params: {entrada_id: entrada.id}}).then(response => {
                    this.posicion = i;
                    this.listadoEntradas = false;
                    this.mostrarDevolucion = true;
                    this.assign_datos(response);
                }).catch(error => {
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                });
            },
            // CONFIRMAR DEVOLUCIÓN
            confirmarDevolucion(){
                if(this.todo_total > 0){
                    this.$refs['modal-confirmarDevolucion'].show();
                } else {
                    this.makeToast('warning', 'El total debe ser mayor a cero.');
                }
            },
            // GUARDAR DEVOLUCIÓN DE LA ENTRADA
            guardarDevolucion(){
                this.entrada.items = this.registros;
                this.load = true;
                axios.post('/entradas/devolucion', this.entrada).then(response => {
                    this.entradas[this.posicion].total_devolucion = response.data.total_devolucion;
                    this.entradas[this.posicion].total_pendiente = response.data.total - (response.data.total_pagos + response.data.total_devolucion);
                    this.$refs['modal-confirmarDevolucion'].hide();
                    this.acumular();
                    this.makeToast('success', 'La devolución se guardo correctamente.');
                    this.listadoEntradas = true;
                    this.mostrarDevolucion = false;
                    this.load = false; 
                }).catch(error => {
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                    this.load = false;
                });
            },
            // GUARDAR REGISTRO TEMPORAL
            guardarRegistro(){
                if(this.temporal.unidades > 0){
                    // this.temporal.unidades = this.unidades;
                    if(this.agregar == false){
                        this.nuevos.push(this.temporal);
                    }
                    this.registros.push(this.temporal);
                    this.total_unidades += parseInt(this.temporal.unidades);
                    this.unidades = null;
                    this.temporal = {};
                    this.isbn = '';
                    this.queryTitulo = '',
                    this.inputISBN = true;
                    this.inputLibro = true;
                    this.inputUnidades = false;
                }
                else{
                    this.makeToast('danger', 'Unidades no validas');
                }
            },
            // ELIMINAR REGISTRO TEMPORAL
            eliminarTemporal(){
                this.temporal = {};
                this.inputISBN = true;
                this.inputLibro = true;
                this.inputUnidades = false;
                this.queryTitulo = '';
                this.unidades = null;
                this.isbn = '';
            },
            ini_1(){
                this.inputLibro = false;
                this.inputISBN = false;
                this.inputUnidades = true;
                this.resultslibros = [];
            },
            acumular(){
                this.total = 0;
                this.entradas.forEach(entrada => {
                    this.total += entrada.unidades;
                });
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
<style scoped>
    #listaLR{
        position: absolute;
        z-index: 100
    }
</style>