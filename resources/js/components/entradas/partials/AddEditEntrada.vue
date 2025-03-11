<template>
    <div>
        <b-row>
            <b-col>
                <h4>{{ agregar ? 'Nueva' : 'Editar' }} entrada</h4>
            </b-col>
            <b-col sm="2" class="text-right">
                <b-button :disabled="load || form.registros.length == 0 || stateN != true ||
                        (form.editorial == 'MAJESTIC EDUCATION' && form.queretaro && total_unidades_que == 0)"
                    @click="confirmarEntrada()" variant="success" pill block>
                    <i class="fa fa-check"></i>  {{ !load ? 'Guardar' : 'Guardando' }}
                </b-button>
            </b-col>
        </b-row>
        <hr>
        <div>
            <b-row>
                <b-col>
                    <b-row>
                        <b-col sm="2"><label>Editorial</label></b-col>
                        <b-col>
                            <b-form-select v-model="form.editorial" autofocus :state="stateE" 
                                @change="editorialSelected()"
                                :disabled="load || form.registros.length > 0" :options="options">
                            </b-form-select>    
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col sm="2"><label>Folio</label></b-col>
                        <b-col>
                            <b-form-input style="text-transform:uppercase;"
                                v-model="form.folio" :disabled="load" :state="stateN"
                                @change="guardarNum()">
                            </b-form-input>
                        </b-col>
                    </b-row>
                </b-col>
                <b-col class="text-right">
                    <div v-if="form.editorial == 'MAJESTIC EDUCATION'">
                        <b-row>
                            <b-col sm="2"><label>Imprenta</label></b-col>
                            <b-col>
                                <b-form-select v-model="form.imprenta_id" autofocus :state="stateE"
                                    :disabled="load || (form.registros.length > 0 && agregar)" :options="imprentas">
                                </b-form-select>    
                            </b-col>
                        </b-row>
                        <b-row class="mt-3" v-if="!form.queretaro">
                            <b-col></b-col>
                            <b-col sm="4">
                                <b-button variant="dark" pill block @click="showScratch()">
                                    Scratch
                                </b-button>
                            </b-col>
                        </b-row>
                    </div>
                </b-col>
            </b-row>
            <hr>
            <b-table :items="form.registros" :fields="form.queretaro ? fieldsQO:( form.total > 0 ? fieldsREcosto:fieldsRE)">
                <template v-slot:cell(index)="row">{{ row.index + 1}}</template>
                <template v-slot:cell(ISBN)="row">{{ row.item.isbn }}</template>
                <template v-slot:cell(titulo)="row">
                    {{ row.item.titulo }}
                    <b-badge v-if="row.item.scratch" variant="info">scratch</b-badge>
                </template>
                <template v-slot:cell(unidades)="row">{{ row.item.unidades | formatNumber }}</template>
                <template v-slot:cell(unidades_que)="row">{{ row.item.unidades_que | formatNumber }}</template>
                <template v-slot:cell(total_unidades)="row">{{ row.item.total_unidades | formatNumber }}</template>
                <template v-slot:cell(costo_unitario)="row">${{ row.item.costo_unitario | formatNumber }}</template>
                <template v-slot:cell(total)="row">${{ row.item.total | formatNumber }}</template>
                <template v-slot:cell(actions)="row">
                    <b-button v-if="!row.item.scratch && row.item.type != 'digital'" pill size="sm"
                        variant="danger" @click="eliminarRegistro(row.item, row.index)">
                        <i class="fa fa-minus-circle"></i>
                    </b-button>
                    <b-button v-if="(agregar && !row.item.scratch) || (!agregar && form.total > 0)" pill size="sm" variant="warning" 
                        @click="editRegistro(row.item, row.index)">
                        <i :class="`fa fa-${position == row.index ? 'spinner':'pencil'}`"></i>
                    </b-button>
                </template>
                <template #thead-top="row">
                    <tr v-if="form.editorial !== null">
                        <th colspan="1"></th>
                        <th>ISBN</th>
                        <th>Libro</th>
                        <th>{{ form.queretaro ? 'Unidades (CDMX)':'Unidades' }}</th>
                        <th v-if="form.queretaro">Unidades (QUE)</th>
                        <th v-if="form.total > 0">Costo unitario</th>
                        <th v-if="form.total > 0">Total</th>
                    </tr>
                    <tr v-if="form.editorial !== null">
                        <th colspan="1"></th>
                        <th>
                            <b-input autofocus v-model="temporal.isbn" :disabled="position != null"
                                @keyup="buscarLibroISBN()"
                            ></b-input>
                            <div class="list-group" v-if="resultsISBNS.length" id="listaLR">
                                <a href="#" v-bind:key="i" class="list-group-item list-group-item-action" 
                                    v-for="(libro, i) in resultsISBNS" @click="datosLibro(libro)">
                                    {{ libro.ISBN }}
                                </a>
                            </div>
                        </th>
                        <th>
                            <b-input style="text-transform:uppercase;" :disabled="position != null"
                                v-model="temporal.titulo" @keyup="mostrarLibros()">
                            </b-input>
                            <div class="list-group" v-if="resultslibros.length" id="listaLR">
                                <a href="#" v-bind:key="i" class="list-group-item list-group-item-action" 
                                    v-for="(libro, i) in resultslibros" @click="datosLibro(libro)">
                                    {{ libro.titulo }}
                                </a>
                            </div>
                        </th>
                        <th>
                            <b-form-input v-model="temporal.unidades" :disabled="temporal.registro_id != null"
                                type="number" required>
                            </b-form-input>
                        </th>
                        <th v-if="form.queretaro">
                            <b-form-input v-model="temporal.unidades_que" 
                                type="number" required>
                            </b-form-input>
                        </th>
                        <th v-if="form.total > 0">
                            <b-form-input v-model="temporal.costo_unitario" 
                                type="number" required>
                            </b-form-input>
                        </th>
                        <th v-if="form.total > 0"></th>
                        <th colspan="2">
                            <b-button :disabled="temporal.id == null" size="sm"
                                variant="success" pill @click="saveTemporal()">
                                <i class="fa fa-level-down"></i>
                            </b-button>
                        </th>
                    </tr>
                    <tr>
                        <th colspan="3"></th>
                        <th>{{ form.unidades | formatNumber }}</th>
                        <th v-if="form.queretaro">{{ total_unidades_que | formatNumber }}</th>
                        <th v-if="form.queretaro">{{ total_unidades | formatNumber }}</th>
                        <th></th>
                        <th>${{ form.total | formatNumber }}</th>
                    </tr>
                </template>
            </b-table>
            <!-- MODALS -->
            <b-modal ref="modal-confirmarEntrada" size="xl" title="Resumen de la entrada" hide-footer>
                <form @submit="onSubmit" enctype="multipart/form-data">
                    <b-row>
                        <b-col>
                            <label><b>Folio:</b> {{form.folio}}</label><br>
                            <label><b>Editorial:</b> {{form.editorial}}</label>
                        </b-col>
                        <b-col class="text-right">
                            <b-form-group v-if="agregar">
                                <input :disabled="load" type="file" id="archivoType" 
                                    v-on:change="fileChange" name="file" multiple>
                                <label for="archivoType">
                                    <i class="fa fa-upload"></i> Subir factura
                                </label>
                                <ul>
                                    <li v-for="(file, i) in form.files" v-bind:key="i">
                                        {{ file.name }}
                                    </li>
                                </ul>
                                <!-- <div v-if="errors && errors.file" class="text-danger">
                                    La foto debe tener un tamaño máximo de 3MB y solo formato jpg, png, jpeg
                                </div> -->
                            </b-form-group>
                            <!-- <subir-foto-component :disabled="load" :allowExt="allowExt"
                                :titulo="'Subir factura'" @uploadImage="uploadImage"></subir-foto-component> -->
                        </b-col>
                    </b-row>
                    <b-table :items="form.registros" :fields="form.queretaro ? fieldsQO:( form.total > 0 ? fieldsREcosto:fieldsRE)">
                        <template v-slot:cell(index)="row">{{ row.index + 1}}</template>
                        <template v-slot:cell(ISBN)="row">{{ row.item.isbn }}</template>
                        <template v-slot:cell(titulo)="row">
                            {{ row.item.titulo }}
                            <b-badge v-if="row.item.scratch" variant="info">scratch</b-badge>
                        </template>
                        <template v-slot:cell(unidades)="row">{{ row.item.unidades | formatNumber }}</template>
                        <template v-slot:cell(unidades_que)="row">{{ row.item.unidades_que | formatNumber }}</template>
                        <template v-slot:cell(total_unidades)="row">{{ row.item.total_unidades | formatNumber }}</template>
                        <template #thead-top="row">
                            <tr>
                                <th colspan="3"></th>
                                <th>{{ form.unidades | formatNumber }}</th>
                                <th v-if="form.queretaro">{{ total_unidades_que | formatNumber }}</th>
                                <th v-if="form.queretaro">{{ total_unidades | formatNumber }}</th>
                                <th></th>
                                <th>${{ form.total | formatNumber }}</th>
                            </tr>
                        </template>
                    </b-table>
                    <b-row>
                        <b-col sm="10">
                            <b-alert show variant="info">
                                <i class="fa fa-exclamation-circle"></i> <b>Verificar los datos de la entrada.</b> En caso de algún error, modificar antes de presionar <b>Confirmar</b> ya que después no se podrán realizar cambios.
                            </b-alert>
                        </b-col>
                        <b-col sm="2" align="right">
                            <b-button type="submit" variant="success" :disabled="load || (form.files.length == 0 && agregar)">
                                <i class="fa fa-check"></i> Confirmar
                            </b-button>
                        </b-col>
                    </b-row>
                </form>
            </b-modal>
            <!-- Agregar Scratch -->
            <b-modal ref="modal-scratch" size="xl" title="Scratch" hide-footer>
                <!-- BUSCAR Y AGREGAR SCRATCH -->
                <add-scratchs-component @addedScratch="addedScratch"></add-scratchs-component>
                <!-- LISTAR PACKS AGREGADOS -->
                <b-table :items="packs" :fields="fieldsScratch">
                    <template v-slot:cell(index)="row">
                        {{ row.index + 1 }}
                    </template>
                    <template v-slot:cell(unidades)="row">
                        {{ row.item.unidades | formatNumber }}
                    </template>
                    <template v-slot:cell(actions)="row">
                        <b-button variant="danger" pill size="sm" @click="deleteScratch(row.item, row.index)">
                            <i class="fa fa-close"></i>
                        </b-button>
                    </template>
                </b-table>
            </b-modal>
        </div>
    </div>
</template>

<script>
import Swal from 'sweetalert2';
import sweetAlert from '../../../mixins/sweetAlert';
import SubirFotoComponent from '../../funciones/SubirFotoComponent.vue';
import formatNumber from './../../../mixins/formatNumber';
import toast from './../../../mixins/toast';
import AddScratchsComponent from '../../funciones/AddScratchsComponent.vue';
import getImprentas from '../../../mixins/editoriales/getImprentas';
import getEditoriales from '../../../mixins/getEditoriales';
export default {
  components: { SubirFotoComponent, AddScratchsComponent },
    props: ['entrada', 'agregar'],
    mixins: [formatNumber, toast, sweetAlert, getImprentas, getEditoriales],
    data(){
        return {
            form: {
                id: 0,
                unidades: 0,
                folio: null,
                editorial: null,
                imprenta_id: null,
                queretaro: false,
                total: 0,
                registros: [],
                files: [],
                eliminados: [],
                packs: []
            },
            stateN: null,
            stateE: null,
            fieldsRE: [
                {key: 'index', label: 'N.'}, 
                {key: 'ISBN', label: 'ISBN'}, 
                {key: 'titulo', label: 'Libro'}, 
                {key: 'unidades', label: 'Unidades'}, 
                {key: 'actions', label: ''}
            ],
            fieldsREcosto: [
                {key: 'index', label: 'N.'}, 
                {key: 'ISBN', label: 'ISBN'}, 
                {key: 'titulo', label: 'Libro'}, 
                {key: 'unidades', label: 'Unidades'}, 
                {key: 'costo_unitario', label: 'Costo unitario'}, 
                {key: 'total', label: 'Total'}, 
                {key: 'actions', label: ''}
            ],
            fieldsQO: [
                {key: 'index', label: 'N.'}, 
                {key: 'ISBN', label: 'ISBN'}, 
                {key: 'titulo', label: 'Libro'}, 
                {key: 'unidades', label: 'Unidades (CDMX)'}, 
                {key: 'unidades_que', label: 'Unidades (QUE)'}, 
                {key: 'total_unidades', label: 'Total'}, 
                {key: 'actions', label: ''}
            ],
            temporal: {
                registro_id: null,
                id: null,
                isbn: null,
                titulo: null,
                unidades: null,
                unidades_que: null,
                costo_unitario: 0,
                total_unidades: 0,
                total: 0
            },
            resultslibros: [],
            resultsISBNS: [],
            showSelect: true,
            total_unidades_que: 0,
            total_unidades: 0,
            allowExt: /(\.jpg|\.jpeg|\.png|\.pdf)$/i,
            packs: [],
            fieldsScratch: [
                { key: 'index', label: 'N.' },
                'titulo', 'unidades',
                { key: 'actions', label: '' }
            ],
            position: null
        }
    },
    created: function(){
        this.get_editoriales();

        if(!this.agregar){
            this.form.id = this.entrada.id;
            this.form.unidades = this.entrada.unidades,
            this.form.folio = this.entrada.folio;
            this.form.editorial = this.entrada.editorial;
            this.form.imprenta_id = this.entrada.imprenta_id;
            this.form.total = this.entrada.total;
            this.getImprentas('all');
            this.stateN = true; // PARA ARPOBAR QUE EL FOLIO ES VALIDO
            this.entrada.registros.forEach(registro => {
                let r = {
                    registro_id: registro.id,
                    id: registro.libro_id, //Esta de esta manera debido a la estructura que se tenia en un inicio al solo crear las entradas
                    pack_id: registro.pack_id,
                    type: registro.libro.type,
                    isbn: registro.libro.ISBN,
                    titulo: registro.libro.titulo,
                    costo_unitario: registro.costo_unitario,
                    total: registro.total,
                    unidades: registro.unidades,
                    unidades_que: registro.unidades_que,
                    total_unidades: registro.unidades + registro.unidades_que,
                    scratch: registro.pack_id != null ? true:false,
                    pack_id: registro.pack_id
                };
                this.form.registros.push(r);
            });
        }
    },
    methods: {
        editorialSelected(){
            if(this.form.editorial == 'MAJESTIC EDUCATION'){
                this.getImprentas('fisico');
                // Swal.fire({
                //     title: "¿Se enviarán libros a Querétaro?",
                //     showDenyButton: true,
                //     confirmButtonText: "SI",
                //     denyButtonText: `NO`
                // }).then((result) => {
                //     if (result.isConfirmed)
                //         this.form.queretaro = true;
                //     else if (result.isDenied)
                //         this.form.queretaro = false;
                // });
            }
            if(this.form.editorial != 'MAJESTIC EDUCATION') {
                this.form.queretaro = false;
                this.form.imprenta_id = null;
            }
            this.inicializar_temporal(null, null, null);
            this.resultsISBNS = [];
            this.resultslibros = [];
        },
        confirmarEntrada(){
            this.form.file = null;
            this.$refs['modal-confirmarEntrada'].show();
        },
        onSubmit(e){
            e.preventDefault();
            this.load = true;
            if(this.agregar){
                let formData = new FormData();
                for (var i = 0; i < this.form.files.length; i++) {
                    let file = this.form.files[i];
                    formData.append('files[]', file);
                }
                formData.append('unidades', this.form.unidades);
                formData.append('folio', this.form.folio);
                formData.append('editorial', this.form.editorial);
                formData.append('imprenta_id', this.form.imprenta_id);
                formData.append('queretaro', this.form.queretaro);
                formData.append('packs', JSON.stringify(this.packs));
                formData.append('registros', JSON.stringify(this.form.registros));

                var ax = axios.post('/entradas/store', formData, { headers: { 'content-type': 'multipart/form-data' } });
            } else {
                this.form.packs = [];
                this.form.packs = this.packs;
                var ax = axios.put('/entradas/update', this.form);
            }

            ax.then(response => {
                this.messageAlert('center', 'success', 'La entrada se guardo correctamente', '/information/entradas/lista', 'close-opener');
                this.load = false;
            }).catch(error => {
                this.load = false;
                this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
            });
        },
        guardarNum(){
            if(this.form.folio.length > 0){
                axios.get('/buscarFolio', {params: {folio: this.form.folio}}).then(response => {
                    if(response.data.id != undefined){
                        this.stateN = false;
                        this.makeToast('warning', 'El folio ya existe.');
                    } else{
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
            if(!this.agregar && !item.nuevo){
                this.form.eliminados.push(item);
            }
            this.restasUnidades(item, i);
        },
        // EDITAR REGISTRO DE ENTRADA
        editRegistro(item, i){
            this.position = i;
            this.inicializar_temporal(item.id, item.titulo, item.isbn, item.registro_id, item.unidades, item.unidades_que, item.total_unidades, item.costo_unitario, item.total);
        },
        restasUnidades(item, i){
            this.form.registros.splice(i, 1);
            this.acum_total();
        },
        buscarLibroISBN(){
            if(this.temporal.isbn.length > 0){
                axios.get('/libro/by_editorial_type_isbn', {params: {isbn: this.temporal.isbn, editorial: this.form.editorial, typeNot: 'digital'}}).then(response => {
                    this.resultsISBNS = response.data;
                }).catch(error => {
                    this.makeToast('warning', 'ISBN es incorrecto o pertenece a otra editorial.');
                });
            } else {
                this.resultsISBNS = [];
            }
        },
        
        inicializar_temporal(id, titulo, ISBN, ri = null, u = 0, uq = 0, tu = 0, cu = 0, t = 0){
            this.temporal.id = id;
            this.temporal.titulo = titulo;
            this.temporal.isbn = ISBN;
            this.temporal.registro_id = ri;
            this.temporal.unidades = u;
            this.temporal.unidades_que = uq;
            this.temporal.total_unidades = tu;
            this.temporal.costo_unitario = cu;
            this.temporal.total = t;
        },
        mostrarLibros(){
            if(this.temporal.titulo.length > 0){ 
                axios.get('/libro/by_editorial_type_titulo', {params: {titulo: this.temporal.titulo, editorial: this.form.editorial, typeNot: 'digital'}}).then(response => {
                    this.resultslibros = response.data;
                }).catch(error => {
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                });
            } else {
                this.resultslibros = [];
            }
        },
        datosLibro(libro){
            this.resultslibros = [];
            this.resultsISBNS = [];
            this.inicializar_temporal(libro.id, libro.titulo, libro.ISBN);
        },
        // AGREGAR LIBRO AL LISTADO GENERAL
        saveTemporal() {
            // COMPROBAR QUE EL LIBRO NO ESTE EN LA LISTA GENERAL
            var check = this.form.registros.find(r => r.id == this.temporal.id);
            if (check == undefined || (check !== undefined && this.position != null)) {
                let u = parseInt(this.temporal.unidades);
                let uq = parseInt(this.temporal.unidades_que);
                let total_unidades = u + uq;
                if (total_unidades > 0) {
                    if(this.agregar || (!this.agregar && this.form.total > 0 && this.temporal.costo_unitario > 0) || 
                        (!this.agregar && this.form.total == 0 && this.temporal.costo_unitario == 0)){
                        let total = total_unidades * this.temporal.costo_unitario;
                        if(this.position == null){
                            this.form.registros.push(this.assign_registro(this.temporal.id, this.temporal.isbn, this.temporal.titulo, u, uq, total_unidades, this.temporal.costo_unitario, total, false, null, this.agregar ? false:true));
                        } else {
                            this.form.registros[this.position].unidades = u;
                            this.form.registros[this.position].total_unidades = total_unidades;
                            this.form.registros[this.position].costo_unitario = this.temporal.costo_unitario;
                            this.form.registros[this.position].total = total; 
                            this.position = null;
                        }
                        this.acum_total();
                        this.inicializar_temporal(null, null, null);
                    } else {
                        this.makeToast('warning', 'El costo unitario debe ser mayor a 0');
                    }
                } else {
                    this.makeToast('warning', 'El total de unidades debe ser mayor a 0');
                }
            } else {
                this.makeToast('warning', 'El libro ya ha sido agregado.');
            }
        },
        assign_registro(id, isbn, titulo, u, uq, total_unidades, costo_unitario, total, scratch, pack_id, nuevo) {
            return {
                id: id,
                isbn: isbn,
                titulo: titulo,
                unidades: u,
                unidades_que: uq,
                total_unidades: total_unidades,
                costo_unitario: costo_unitario,
                total: total,
                scratch: scratch,
                pack_id: pack_id,
                nuevo: nuevo
            }
        },
        acum_total(){
            this.form.unidades = 0;
            this.total_unidades_que = 0;
            this.form.total = 0;
            this.form.registros.forEach(registro => {
                this.form.unidades += parseInt(registro.unidades);
                this.total_unidades_que += parseInt(registro.unidades_que);
                this.form.total += parseInt(registro.total);
            });
            this.total_unidades = this.form.unidades + this.total_unidades_que;
        },
        fileChange(e){
            var fileInput = document.getElementById('archivoType');
            
            if(this.allowExt.exec(fileInput.value)){
                let files = e.target.files || e.dataTransfer.files;
                if (!files.length) return;
                
                for (let i = files.length - 1; i >= 0; i--) {
                    this.form.files.push(files[i]);
                }
                document.getElementById("archivoType").value = [];
            } else {
                this.messageAlert('center', 'warning', 'Revisar formato de archivo. Formato de archivo no permitido', null, 'info');
            }
        },
        // MOSTRAR MODAL DE SCRATCH
        showScratch() {
            this.$refs['modal-scratch'].show();
        },
        // AGREGAR PACK A LA LISTA
        addedScratch(temporal) {
            // VERIFICA SI EL LIBRO DIGITAL O LIBRO FISICO NO SE HA AGREGADO A LA ENTRADA
            var check_f = this.form.registros.find(d => d.id == temporal.libro_fisico);
            var check_d = this.form.registros.find(d => d.id == temporal.libro_digital);
            // VERIFICA SI EL PACK NO SE A AGREADO A LA LISTA
            var check = this.packs.find(p => p.id == temporal.id);
            // SI LAS TRES CONDIFICONES SON UNDEFINES, CONTINUA
            if (check == undefined && check_f == undefined && check_d == undefined) { 
                // SE VERIFICAR QUE LAS UNIDADES SEAN MAYOR QUE 0
                if (temporal.unidades > 0) {
                    // GET PARA OBTENER LIBRO FISICO Y DIGITAL DEL PACK SELECCIONADO
                    axios.get('/libro/scratch_libros', { params: { f: temporal.libro_fisico, d: temporal.libro_digital }
                    }).then(response => {
                        // SE AGREGA EL PACK AL ARRAY PACKS
                        this.packs.push(temporal);
                        response.data.forEach(r => {
                            // AGREGAR LOS 2 LIBROS A LA LISTA PRINCIPAL Y SUMAR UNIDADES
                            this.form.registros.push(this.assign_registro(r.id, r.ISBN, r.titulo, temporal.unidades, 0, temporal.unidades, 0, 0, true, temporal.id, this.agregar ? false:true));
                            this.acum_total();
                        });
                    }).catch(error => { });
                } else {
                    this.makeToast('warning', 'Las unidades deben ser mayor a 0.');
                }
            } else {
                this.makeToast('warning', 'El libro ya ha sido agregado.');
            }
        },
        // ELIMINAR PACK DE LA LISTA
        deleteScratch(pack, position) {
            let positions = [];
            // OBTENER POSICIONES DEL LIBRO FISICO Y DIGITAL
            this.form.registros.findIndex(function (value, index) {
                if (value.pack_id == pack.id) positions.push(index);
            });
            // ELIMINAR AMBOS LIBROS DE LA LISTA GENERAL, EMPEZANDO DESDE EL ULTIMO
            positions.reverse()
            positions.forEach(p => {
                this.form.registros.splice(p, 1);
            });

            // ELIMAR DE LISTADO DE PACKS
            this.packs.splice(position, 1);
            // RESTAR EL TOTAL DE UNIDADES
            this.acum_total();
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