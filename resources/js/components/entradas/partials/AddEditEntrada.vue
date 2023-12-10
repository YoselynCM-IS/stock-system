<template>
    <div>
        <b-row>
            <b-col sm="3">
                <h4 style="color: #170057">{{ agregar ? 'Nueva' : 'Editar' }} entrada</h4>
            </b-col>
            <b-col sm="6" class="text-right">
                <b-button :disabled="load || form.registros.length == 0 || stateN != true ||
                        (form.editorial == 'MAJESTIC EDUCATION' && form.queretaro && total_unidades_que == 0)"
                    @click="confirmarEntrada()" variant="success" pill>
                    <i v-if="agregar === true" class="fa fa-check"> {{ !load ? 'Guardar' : 'Guardando' }}</i>
                    <i v-else class="fa fa-check"> {{ !load ? 'Guardar  cambios' : 'Guardando' }}</i>
                </b-button>
            </b-col>
            <b-col sm="3" class="text-right">
                <!-- PENDIENTE EL BOTON DE REGRESAR -->
                <b-button variant="secondary" pill @click="goBack()">
                    <i class="fa fa-mail-reply"></i> Regresar
                </b-button>
            </b-col>
        </b-row>
        <hr>
        <div>
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
                        <b-row v-if="form.editorial == 'MAJESTIC EDUCATION'">
                            <b-col sm="2"><label>Imprenta</label></b-col>
                            <b-col>
                                <b-form-select v-model="form.imprenta_id" autofocus :state="stateE"
                                    :disabled="load || form.registros.length > 0" :options="imprentas">
                                </b-form-select>    
                            </b-col>
                        </b-row>
                    </b-col>
                </b-row>
            </div>
            <hr>
            <b-table :items="form.registros" :fields="form.queretaro ? fieldsQO:fieldsRE">
                <template v-slot:cell(index)="row">{{ row.index + 1}}</template>
                <template v-slot:cell(ISBN)="row">{{ row.item.isbn }}</template>
                <template v-slot:cell(titulo)="row">{{ row.item.titulo }}</template>
                <template v-slot:cell(unidades)="row">{{ row.item.unidades | formatNumber }}</template>
                <template v-slot:cell(unidades_que)="row">{{ row.item.unidades_que | formatNumber }}</template>
                <template v-slot:cell(total_unidades)="row">{{ row.item.total_unidades | formatNumber }}</template>
                <template v-slot:cell(eliminar)="row">
                    <b-button v-if="agregar == true" pill
                        variant="danger" @click="eliminarRegistro(row.item, row.index)">
                        <i class="fa fa-minus-circle"></i>
                    </b-button>
                </template>
                <template #thead-top="row">
                    <tr v-if="form.editorial !== null">
                        <th colspan="1"></th>
                        <th>ISBN</th>
                        <th>Libro</th>
                        <th>{{ form.queretaro ? 'Unidades (CDMX)':'Unidades' }}</th>
                        <th v-if="form.queretaro">Unidades (QUE)</th>
                    </tr>
                    <tr v-if="form.editorial !== null">
                        <th colspan="1"></th>
                        <th>
                            <b-input autofocus v-model="temporal.isbn" 
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
                            <b-input style="text-transform:uppercase;"
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
                            <b-form-input v-model="temporal.unidades" 
                                type="number" required>
                            </b-form-input>
                        </th>
                        <th v-if="form.queretaro">
                            <b-form-input v-model="temporal.unidades_que" 
                                type="number" required>
                            </b-form-input>
                        </th>
                        <th v-if="form.queretaro" colspan="2">
                            <b-button :disabled="temporal.id == null"
                                variant="success" pill @click="saveTemporal()">
                                <i class="fa fa-level-down"></i>
                            </b-button>
                        </th>
                        <th v-else>
                            <b-button :disabled="temporal.id == null || temporal.unidades <= 0"
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
                            <b-form-group>
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
                    <b-table :items="form.registros" :fields="form.queretaro ? fieldsQO:fieldsRE">
                        <template v-slot:cell(index)="row">{{ row.index + 1}}</template>
                        <template v-slot:cell(ISBN)="row">{{ row.item.isbn }}</template>
                        <template v-slot:cell(titulo)="row">{{ row.item.titulo }}</template>
                        <template v-slot:cell(unidades)="row">{{ row.item.unidades | formatNumber }}</template>
                        <template v-slot:cell(unidades_que)="row">{{ row.item.unidades_que | formatNumber }}</template>
                        <template v-slot:cell(total_unidades)="row">{{ row.item.total_unidades | formatNumber }}</template>
                        <template #thead-top="row">
                            <tr>
                                <th colspan="3"></th>
                                <th>{{ form.unidades | formatNumber }}</th>
                                <th v-if="form.queretaro">{{ total_unidades_que | formatNumber }}</th>
                                <th v-if="form.queretaro">{{ total_unidades | formatNumber }}</th>
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
                            <b-button type="submit" variant="success" :disabled="load || form.files.length == 0">
                                <i class="fa fa-check"></i> Confirmar
                            </b-button>
                        </b-col>
                    </b-row>
                </form>
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
export default {
  components: { SubirFotoComponent },
    props: ['agregar', 'form', 'editoriales'],
    mixins: [formatNumber, toast, sweetAlert],
    data(){
        return {
            load: false,
            stateN: null,
            stateE: null,
            options: [],
            fieldsRE: [
                {key: 'index', label: 'N.'}, 
                {key: 'ISBN', label: 'ISBN'}, 
                {key: 'titulo', label: 'Libro'}, 
                'unidades', 
                {key: 'eliminar', label: ''}
            ],
            fieldsQO: [
                {key: 'index', label: 'N.'}, 
                {key: 'ISBN', label: 'ISBN'}, 
                {key: 'titulo', label: 'Libro'}, 
                {key: 'unidades', label: 'Unidades (CDMX)'}, 
                {key: 'unidades_que', label: 'Unidades (QUE)'}, 
                {key: 'total_unidades', label: 'Total'}, 
                {key: 'eliminar', label: ''}
            ],
            temporal: {
                id: null,
                isbn: null,
                titulo: null,
                unidades: null,
                unidades_que: null,
                total_unidades: 0,
            },
            resultslibros: [],
            resultsISBNS: [],
            showSelect: true,
            total_unidades_que: 0,
            total_unidades: 0,
            imprentas: [],
            allowExt: /(\.jpg|\.jpeg|\.png|\.pdf)$/i
        }
    },
    created: function(){
        this.options.push({
            value: null,
            text: 'Selecciona una opción'
        });
        this.editoriales.forEach(editorial => {
            this.options.push({
                value: editorial.editorial,
                text: editorial.editorial
            });
        });
    },
    methods: {
        editorialSelected(){
            if(this.form.editorial == 'MAJESTIC EDUCATION'){
                this.getImprentas();
                Swal.fire({
                    title: "¿Se enviarán libros a Querétaro?",
                    showDenyButton: true,
                    confirmButtonText: "SI",
                    denyButtonText: `NO`
                }).then((result) => {
                    if (result.isConfirmed)
                        this.form.queretaro = true;
                    else if (result.isDenied)
                        this.form.queretaro = false;
                });
            }
            if(this.form.editorial != 'MAJESTIC EDUCATION') {
                this.form.queretaro = false;
                this.form.imprenta_id = null;
            }
            this.inicializar_temporal(null, null, null);
            this.resultsISBNS = [];
            this.resultslibros = [];
        },
        getImprentas(){
            this.load = true;
            this.imprentas = [];
            axios.get('/entradas/get_imprentas').then(response => {
                let is = response.data;
                this.imprentas.push({
                    value: null,
                    text: 'Selecciona una opción'
                });
                is.forEach(i => {
                    this.imprentas.push({
                        value: i.id,
                        text: i.imprenta
                    });
                });
                this.load = false;
            }).catch(error => {
                this.load = false;
            });
        },
        confirmarEntrada(){
            this.form.file = null;
            this.$refs['modal-confirmarEntrada'].show();
        },
        onSubmit(e){
            e.preventDefault();
            this.load = true;

            let formData = new FormData();
            // formData.append('file', this.form.file, this.form.file.name);
            for (var i = 0; i < this.form.files.length; i++) {
                let file = this.form.files[i];
                formData.append('files[]', file);
            }
            formData.append('unidades', this.form.unidades);
            formData.append('folio', this.form.folio);
            formData.append('editorial', this.form.editorial);
            formData.append('imprenta_id', this.form.imprenta_id);
            formData.append('queretaro', this.form.queretaro);
            formData.append('registros', JSON.stringify(this.form.registros));
            axios.post('/entradas/store', formData, { 
                headers: { 'content-type': 'multipart/form-data' } })
                .then(response => {
                this.messageAlert('center', 'success', 'La entrada se creo correctamente', null, 'reload');
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
            this.restasUnidades(item, i);
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
        inicializar_temporal(id, titulo, ISBN){
            this.temporal.id = id;
            this.temporal.titulo = titulo;
            this.temporal.isbn = ISBN;
            this.temporal.unidades = 0;
            this.temporal.unidades_que = 0;
            this.temporal.total_unidades = 0;
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
        saveTemporal(){
            let u = parseInt(this.temporal.unidades);
            let uq = parseInt(this.temporal.unidades_que);
            let total_unidades = u + uq;
            if(total_unidades > 0){
                this.form.registros.push({
                    id: this.temporal.id,
                    isbn: this.temporal.isbn,
                    titulo: this.temporal.titulo,
                    unidades: u,
                    unidades_que: uq,
                    total_unidades: total_unidades
                });
                this.acum_total();
                this.inicializar_temporal(null, null, null);
            } else {
                this.makeToast('warning', 'El total de unidades debe ser mayor a 0');
            }
        },
        acum_total(){
            this.form.unidades = 0;
            this.total_unidades_que = 0;
            this.form.registros.forEach(registro => {
                this.form.unidades += parseInt(registro.unidades);
                this.total_unidades_que += parseInt(registro.unidades_que);
            });
            this.total_unidades = this.form.unidades + this.total_unidades_que;
        },
        goBack(){
            this.$emit('goBack', true);
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