<template>
    <div>
        <b-row>
            <b-col>
                <h6><b>Salida</b></h6>
                <b-input v-model="form.folio" autofocus required
                        placeholder="FOLIO DE LA SALIDA"></b-input>
                <div v-if="errors && errors.folio" class="text-danger">{{ errors.folio[0] }}</div>
            </b-col>
            <b-col sm="4"></b-col>
            <b-col sm="2" class="text-right">
                <b-button pill variant="success" @click="saveSalida()"
                    :disabled="load">
                    <i class="fa fa-check"></i> Guardar
                </b-button>
            </b-col>
        </b-row>
        <b-table :items="form.libros" :fields="fields">
            <template v-slot:cell(index)="row">{{ row.index + 1 }}</template>
            <template v-slot:cell(actions)="row">
                <b-button variant="warning" @click="editRegistro(row.item, row.index)" 
                    pill size="sm" :disabled="load">
                    <i class="fa fa-pencil"></i>
                </b-button>
                <b-button variant="danger" @click="eliminarRegistro(row.index)" 
                    pill size="sm" :disabled="load">
                    <i class="fa fa-minus-circle"></i>
                </b-button>
            </template>
            <template #thead-top="row">
                    <tr>
                        <th colspan="1"></th>
                        <th>ISBN</th>
                        <th>Libro</th>
                        <th>Unidades</th>
                        <th colspan="1"></th>
                    </tr>
                    <tr>
                        <th colspan="1"></th>
                        <th>
                            <b-input :disabled="load" v-model="temporal.isbn"
                                @keyup.enter="buscarLibroISBN()"
                            ></b-input>
                        </th>
                        <th>
                            <b-input style="text-transform:uppercase;"
                                v-model="temporal.titulo"
                                @keyup="mostrarLibros()" :disabled="load">
                            </b-input>
                            <div class="list-group" v-if="resultslibros.length" id="listaL">
                                <a href="#" class="list-group-item list-group-item-action" 
                                    v-for="(libro, i) in resultslibros" v-bind:key="i" 
                                    @click="datosLibro(libro)">
                                    {{ libro.titulo }}
                                </a>
                            </div>
                        </th>
                        <th>
                            <b-form-input v-model="temporal.unidades" 
                                type="number" required :disabled="load">
                            </b-form-input>
                        </th>
                        <th>
                            <b-button variant="success" @click="saveTemporal()" 
                                :disabled="load || temporal.id == null" pill>
                                <i class="fa fa-level-down"></i>
                            </b-button>
                        </th>
                    </tr>
                    <!-- <tr>
                        <th colspan="3"></th>
                        <th>{{ regalo.unidades | formatNumber }}</th>
                        <th></th>
                    </tr> -->
            </template>
        </b-table>
    </div>
</template>

<script>
import sweetAlert from '../../mixins/sweetAlert';
import toast from './../../mixins/toast';
export default {
    mixins: [toast, sweetAlert],
    data(){
        return {
            form: {
                folio: null,
                libros: []
            },
            fields: [
                {key: 'index', label: 'N.'}, 
                {key: 'ISBN', label: 'ISBN'}, 
                {key: 'titulo', label: 'Libro'}, 
                {key: 'unidades', label: 'Unidades'}, 
                {key: 'actions', label: ''}
            ],
            load: false,
            temporal: {},
            position: null,
            errors: {},
            resultslibros: []
        }
    },
    methods: {
        // BUSCAR LIBRO POR ISBN
        buscarLibroISBN(){
            this.load = true;
            axios.get('/libro/by_editorial_type_isbn', {params: {isbn: this.temporal.isbn, editorial: 'MAJESTIC EDUCATION', typeNot: 'digital'}}).then(response => {
                this.datosLibro(response.data[0]);
                this.load = false;
            }).catch(error => {
                this.makeToast('danger', 'ISBN incorrecto');
                this.load = false;
            });
        },
        // MOSTRAR COINCIDENCIA DE LIBROS
        mostrarLibros(){
            if(this.temporal.titulo.length > 0){
                axios.get('/libro/by_editorial_type_titulo', {params: {titulo: this.temporal.titulo, editorial: 'MAJESTIC EDUCATION', typeNot: 'digital'}}).then(response => {
                    this.resultslibros = response.data;
                }).catch(error => {
                    // this.makeToast('danger', 'ISBN incorrecto');
                });
            }
        },
        // ASIGNAR DATOS DEL LIBRO SELECCIONADO
        datosLibro(libro, unidades = 0){
            this.temporal.id = libro.id;
            this.temporal.isbn = libro.ISBN;
            this.temporal.titulo = libro.titulo;
            this.temporal.piezas = libro.piezas;
            this.temporal.unidades = unidades;
            this.resultslibros = [];
        },
        // GUARDAR REGISTRO TEMPORAL
        saveTemporal(){
            if(this.temporal.unidades > 0){
                if(this.temporal.unidades <= this.temporal.piezas){
                    if(this.position == null){
                        this.form.libros.push(this.set_datos());
                    } else {
                        this.form.libros[this.position].id = this.set_datos().id;
                        this.form.libros[this.position].isbn = this.set_datos().isbn;
                        this.form.libros[this.position].titulo = this.set_datos().titulo;
                        this.form.libros[this.position].piezas = this.set_datos().piezas;
                        this.form.libros[this.position].unidades = this.set_datos().unidades;
                    }
                    this.temporal.id = null;
                    this.temporal.isbn = null;
                    this.temporal.titulo = null;
                    this.temporal.piezas = null;
                    this.temporal.unidades = null;
                    this.position = null;
                } else {
                    this.makeToast('warning', `Solo hay ${this.temporal.piezas} unidades en existencia.`);
                }
            } else {
                this.makeToast('warning', 'Las unidades tienen que ser mayor a 0.');
            }
        },
        // EDITAR REGISTRO
        editRegistro(registro, index){
            this.datosLibro(registro, registro.unidades);
            this.position = index;
        },
        // ELIMINAR REGISTRO
        eliminarRegistro(index){
            this.form.libros.splice(index, 1);
        },
        set_datos(){
            return {
                id: this.temporal.id,
                ISBN: this.temporal.isbn,
                titulo: this.temporal.titulo,
                piezas: this.temporal.piezas,
                unidades: this.temporal.unidades
            };
        },
        // GUARDAR
        saveSalida(){
            this.load = true;
            axios.post('/salidas/store', this.form).then(response => {
                this.messageAlert('center', 'success', 'La salida se guardo correctamente.', null, 'reload');
            })
            .catch(error => {
                this.load = false;
                if (error.response.status === 422) {
                    this.errors = error.response.data.errors || {};
                } else {
                    this.makeToast('warning', 'El folio ya existe');
                }
            });
        }
    }
}
</script>

<style>

</style>