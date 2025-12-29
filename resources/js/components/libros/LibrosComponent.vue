<template>
    <div>
        <check-connection-component></check-connection-component>
        <div class="row">
            <!-- BUSCAR LIBROS POR EDITORIAL -->
            <div class="col-md">
                <b-form-group label="Editorial" label-class="font-weight-bold">
                    <b-form-select v-model="queryEditorial" :options="options" @change="http_editorial()"></b-form-select>
                </b-form-group>
            </div>
            <div class="col-md">
                <!-- BUSCAR LIBRO POR TITULO -->
                <b-form-group label="Libro" label-class="font-weight-bold">
                    <b-input style="text-transform:uppercase;" v-model="queryTitulo"
                            @keyup="http_titulo()"></b-input>
                </b-form-group>
            </div>
            <div class="col-md">
                <!-- BUSCAR LIBRO POR ISBN -->
                <b-form-group label="ISBN" label-class="font-weight-bold">
                    <b-input v-model="isbn" @keyup="http_isbn()">
                        </b-input>
                </b-form-group>
            </div>
        </div>
        <div class="row mb-3">
             <div class="col-md">
                <!-- BUSCAR LIBRO POR SERIE -->
                <b-form-group label="Serie" label-class="font-weight-bold">
                    <b-input v-model="querySerie" @keyup="showSeries()" style="text-transform:uppercase;"></b-input>
                    <div class="list-group" v-if="resultsSeries.length > 0" id="listaL">
                        <a href="#" v-bind:key="i" class="list-group-item list-group-item-action"
                            v-for="(serie, i) in resultsSeries" @click="bySerie(serie)">
                            {{ serie.serie }}
                        </a>
                    </div>
                </b-form-group>
            </div>
            <!-- BUSCAR LIBROS POR TIPO -->
            <div class="col-md">
                <b-form-group label="Tipo" label-class="font-weight-bold">
                    <b-form-select v-model="queryType" :options="optionsType" @change="http_type()"></b-form-select>
                </b-form-group>
            </div>
            <div class="col-md-1 text-right">
                <b-button :variant="`${!applyAll ? 'dark':'primary'}`" pill block class="mt-4" @click="applyFilters()">
                    <i class="fa fa-filter"></i>
                </b-button>
            </div>
            <div class="col-md-1 text-left">
                <b-button variant="dark" pill block class="mt-4" :disabled="!applyAll" @click="http_applyAll()">
                    <i class="fa fa-search"></i>
                </b-button>
            </div>
            <div class="col-md-2">
                <!-- DESCARGAR LIBROS -->
                <b-button :href="`/libro/download/list_libros/${this.queryEditorial}/${this.serie_id}/${this.queryTitulo}/${this.isbn}/${this.queryType}`" :disabled="!applyAll" variant="dark"
                    pill block class="mt-4">
                    <i class="fa fa-download"></i> Descargar
                </b-button>
            </div>
            <div class="col-md-2" v-if="role_id === 1 || role_id === 2 || role_id === 3 || role_id == 6 || role_id == 10">
                <!-- AGREGAR UN NUEVO LIBRO -->
                <b-button variant="success" pill block @click="addEditarLibro(null, null, true)" class="mt-4">
                    <i class="fa fa-plus"></i> Nuevo libro
                </b-button>
            </div>
        </div>
        <div v-if="!load">
            <!-- LISTADO DE LIBROS-->
             <vue-good-table :columns="fields" :rows="libros" :line-numbers="true" theme="polar-bear" styleClass="vgt-table condensed"
                :totalRows="librosData.total"
                :pagination-options="{
                    enabled: true,
                    mode: 'remote',
                    perPage: librosData.per_page,
                    setCurrentPage: librosData.current_page,
                    perPageDropdownEnabled: false,
                }"
                @on-page-change="onPageChange">
                <template slot="table-row" slot-scope="props">
                    <div v-if="props.column.field == 'accion'">
                        <b-button v-if="(role_id == 6 || role_id == 1) && props.row.externo == false" style="color:white;"
                            variant="dark" pill size="sm" @click="addEditarLibro(props.row, props.index, false)">
                            <i class="fa fa-pencil"></i>
                        </b-button>
                        <!-- DESHABILITAR TEMPORALMENTE, PARA REVISION DE SEPARACIÓN DE SCRATCH -->
                        <!-- <b-button v-if="(role_id == 6 || role_id == 1 || role_id == 10) && (props.row.piezas > 0)"
                            variant="dark" pill @click="addDefectuosos(props.row)" size="sm">
                            <i class="fa fa-minus"></i>
                        </b-button> -->
                        <b-button v-if="(role_id == 6 || role_id == 1)" variant="danger" pill
                            @click="inactivarLibro(props.row)" size="sm">
                            <i class="fa fa-close"></i>
                        </b-button>
                    </div>
                </template>
            </vue-good-table>
        </div>
        <div v-else class="text-center text-info my-2 mt-3">
            <b-spinner class="align-middle"></b-spinner>
            <strong>Cargando...</strong>
        </div>

        <!-- MODAL PARA AGREGAR UN LIBRO -->
        <b-modal id="modal-newEditLibro" :title="`${addEditLibro ? 'Nuevo':'Editar'} libro`">
            <new-edit-libro-component @actualizarLista="actualizarLista" 
                :form="addEditForm" :addEdit="addEditLibro" :listEditoriales="listEditoriales"></new-edit-libro-component>
            <div slot="modal-footer"></div>
        </b-modal>
        <!-- MODAL PARA AGREGAR DEFECTUOSOS -->
        <b-modal id="modal-defectuosos" :title="form.libro" hide-footer size="sm">
            <add-defectuosos-component @saveDefectuosos="saveDefectuosos"></add-defectuosos-component>
        </b-modal>
    </div>
</template>

<script>
import sweetAlert from '../../mixins/sweetAlert';
import getSeries from '../../mixins/libros/getSeries';
import AddDefectuososComponent from './AddDefectuososComponent.vue';
    export default {
        components: { AddDefectuososComponent },
        props: ['role_id', 'editoriales', 'types'],
        mixins: [sweetAlert, getSeries],
        data() {
            return {
                formlibro: {},
                librosData: {},
                libros: [],
                errors: {},
                posicion: 0,
                loaded: false,
                success: false,
                queryTitulo: null,
                queryEditorial: null,
                fields: [
                    {field: 'editorial', label: 'Editorial'},
                    {field: 'serie', label: 'Serie'},
                    {field: 'ISBN', label: 'ISBN'},
                    {field: 'titulo', label: 'Libro'},
                    {field: 'type', label: 'Tipo'},
                    {field: 'piezas', label: 'Piezas', type: 'number'},
                    {field: 'defectuosos', label: 'Defectuosos', type: 'number'},
                    {field: 'accion', label: '', sortable: false}
                ],
                options: [{
                    value: null, text: 'Seleccionar una opción', disabled: true
                }],
                listEditoriales: [],
                loadRegisters: false,
                isbn: null,
                libro: {},
                sTLibro: false,
                sTIsbn: false,
                sEditorial: false,
                sTSerie: false,
                sTType: null,
                applyAll: false,
                form: {
                    id: null,
                    libro: null,
                    defectuosos: 0,
                    motivo: null
                },
                load: true,
                addEditLibro: true,
                addEditForm: {
                    id: null,
                    serie: {
                        id: null,
                        serie: null
                    },
                    type: null,
                    titulo: null,
                    ISBN: null,
                    autor: null,
                    editorial: null
                },
                queryType: null,
                optionsType: [ {
                    value: null, text: 'Seleccionar una opción', disabled: true
                }],
                serie_id: null
            }
        },
        created: function(){
            this.getResults();
            this.assign_editorial();
            this.assign_types();
        },
        filters: {
            formatNumber: function (value) {
                return numeral(value).format("0,0[.]00"); 
            }
        },
        methods: {
            getResults(page = 1){
                if(!this.applyAll){
                    if(!this.sTLibro && !this.sTIsbn && !this.sTEditorial && !this.sTSerie && !this.sTType)
                        this.http_libros(page);
                    if(this.sTLibro) 
                        this.http_titulo(page);
                    if(this.sTIsbn)
                        this.http_isbn(page);
                    if(this.sTEditorial)
                        this.http_editorial(page);
                    if(this.sTSerie)
                        this.http_serie(page);
                    if(this.sTType)
                        this.http_type(page);
                } else {
                    this.http_applyAll(page);
                }
            },
            onPageChange(params){
                this.getResults(params.currentPage);
            },
            // HTTP REMCLIENTE
            http_libros(page = 1){
                if(!this.applyAll){
                    this.load = true;
                    axios.get(`/libro/index?page=${page}`).then(response => {
                        this.set_response(response.data);
                        this.assign_values(false, false, false, false, false);
                        this.load = false;   
                    }).catch(error => {
                        this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                        this.load = false;
                    });
                }
            },
            // HTTP LIBROS
            http_titulo(page = 1){
                if(!this.applyAll && this.queryTitulo.length > 3){
                    this.load = true;
                    axios.get(`/libro/by_titulo?page=${page}`, {params: {titulo: this.queryTitulo}}).then(response => {
                        this.set_response(response.data);
                        this.assign_values(true, false, false, false, false);
                        this.load = false;
                    }).catch(error => {
                        this.load = false;
                        this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                    });
                }
            },
            // BUSCAR LIBRO POR ISBN
            http_isbn(page = 1) {
                if(!this.applyAll && this.isbn.length > 3){
                    this.load = true;
                    axios.get(`/libro/by_isbn?page=${page}`, {params: {isbn: this.isbn}}).then(response => {
                        this.set_response(response.data);
                        this.assign_values(false, true, false, false, false);
                        this.load = false;
                    }).catch(error => {
                        this.load = false;
                        this.makeToast('danger', 'ISBN incorrecto');
                    });
                }
            },
            // MOSTRAR LIBROS POR EDITORIAL
            http_editorial(page = 1){
                if(!this.applyAll){
                    this.load = true;
                    axios.get(`/libro/by_editorial?page=${page}`, {params: {editorial: this.queryEditorial}}).then(response => {
                        this.set_response(response.data);
                        this.assign_values(false, false, true, false, false);
                        this.load = false;
                    }).catch(error => {
                        this.load = false;
                        this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                    });
                }
            },
            // ASIGNAR VARIABLE SERIE
            bySerie(serie){
                this.serie_id = serie.id;
                this.querySerie = serie.serie;
                this.resultsSeries = [];
                if(!this.applyAll) this.http_serie();
            },
            // OBTENER LIBROS POR SERIE
            http_serie(page = 1){
                this.load = true;
                axios.get(`/libro/by_serie?page=${page}`, {params: {serie_id: this.serie_id}}).then(response => {
                    this.set_response(response.data);
                    this.assign_values(false, false, false, true, false);
                    this.load = false;
                }).catch(error => {
                    this.load = false;
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                });
            },
            // MOSTAR LIBROS POR TIPO
            http_type(page = 1){
                if(!this.applyAll) {
                    this.load = true;
                    axios.get(`/libro/by_type?page=${page}`, {params: {type: this.queryType}}).then(response => {
                        this.set_response(response.data);
                        this.assign_values(false, false, false, false, true);
                        this.load = false;
                    }).catch(error => {
                        this.load = false;
                        this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                    });
                }
            },
            // APLICAR TODOS LOS FILTROS
            http_applyAll(page = 1){
                this.load = true;
                axios.get(`/libro/by_all?page=${page}`, {params: {
                    titulo: this.queryTitulo,
                    isbn: this.isbn,
                    editorial: this.queryEditorial,
                    serie_id: this.serie_id,
                    type: this.queryType
                }}).then(response => {
                    this.set_response(response.data);
                    this.load = false;
                }).catch(error => {
                    this.load = false;
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                });
            },
            // ACTIVAR / DESACTIVAR FILTROS
            applyFilters(){
                this.applyAll = !this.applyAll;
                this.assign_values(false, false, false, false, false);
                if(!this.applyAll) {
                    this.queryTitulo = null;
                    this.isbn = null;
                    this.queryEditorial = null;
                    this.querySerie = null;
                    this.serie_id = null;
                    this.queryType = null;
                }
            },
            // ASIGNAR RESULTADOS DE BUSQUEDA
            set_response(response){
                this.libros = response.libros;
                this.librosData = response.paginate;
            },
            // ASIGNAR VALORES PARA CADA BUSQUEDA
            assign_values(sTLibro, sTIsbn, sTEditorial, sTSerie, sTType){
                this.sTLibro = sTLibro;
                this.sTIsbn = sTIsbn;
                this.sTEditorial = sTEditorial;
                this.sTSerie = sTSerie;
                this.sTType = sTType;
            },
            // ASIGNAR VALORES DE EDITORIALES A ARRAY
            assign_editorial(){
                this.editoriales.forEach(editorial => {
                    this.options.push({
                        value: editorial.editorial,
                        text: editorial.editorial
                    });
                }); 

                var check = this.editoriales.length >= 2;
                this.listEditoriales.push({ value: null, text: 'Seleccionar opción', disabled: true });
                this.editoriales.forEach(editorial => {
                    if(editorial.editorial == 'MAJESTIC EDUCATION' && check) var d = true;
                    else var d = false;
                    
                    this.listEditoriales.push({
                        value: editorial.editorial,
                        text: editorial.editorial,
                        disabled: d
                    });
                });
            },
            // ASIGNAR VALORES DE TYPE A ARRAY
            assign_types(){
                this.types.forEach(type => {
                    this.optionsType.push({
                        value: type,
                        text: type
                    });
                }); 
            },
            // INICIALIZAR PARA EDITAR LIBRO
            addEditarLibro(libro, i, addEdit){
                this.addEditLibro = addEdit;
                this.posicion = i;
                if(this.addEditLibro){
                    this.assign_addEditForm(null, null, null, null, null, null, null, null)
                } else {
                    this.assign_addEditForm(libro.id, libro.serie_id, libro.serie, libro.type, libro.titulo, libro.ISBN, libro.autor, libro.editorial)
                }
                this.$bvModal.show('modal-newEditLibro');
            },
            // ASIGNAR VALORES A addEditForm
            assign_addEditForm(id, serie_id, serie, type, titulo, isbn, autor, editorial){
                this.addEditForm.id = id;
                this.addEditForm.serie.id = serie_id;
                this.addEditForm.serie.serie = serie;
                this.addEditForm.type = type;
                this.addEditForm.titulo = titulo;
                this.addEditForm.ISBN = isbn;
                this.addEditForm.autor = autor;
                this.addEditForm.editorial = editorial;
            },
            // AGREGAR LIBRO AL LISTADO (EVENTO)
            actualizarLista(libro){
                if(this.addEditLibro){
                    this.libros.unshift(libro);
                } else {
                    this.libros[this.posicion].type = libro.type;
                    this.libros[this.posicion].ISBN = libro.ISBN;
                    this.libros[this.posicion].titulo = libro.titulo;
                    this.libros[this.posicion].editorial = libro.editorial;
                    this.libros[this.posicion].autor = libro.autor;
                    this.libros[this.posicion].edicion = libro.edicion;
                    this.libros[this.posicion].serie_id = libro.serie_id;
                    this.libros[this.posicion].serie = libro.serie;
                }
                this.makeToast('success', `El libro se ${this.addEditLibro ? 'agrego':'modifico'} correctamente.`);
                this.$bvModal.hide('modal-newEditLibro');
            },
            // ELIMINAR LIBRO (FUNCIÓN NO UTILIZADA)
            // eliminarLibro(){
            //     axios.delete('/eliminar_libro', {params: {id: this.formlibro.id}}).then(response => {
            //         this.$bvModal.hide('modal-eliminar');
            //     })
            //     .catch(error => {
            //         this.loaded = false;
            //         this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
            //     });
            // },
            makeToast(variant = null, descripcion) {
                this.$bvToast.toast(descripcion, {
                    title: 'Mensaje',
                    variant: variant,
                    solid: true
                })
            },
            inactivarLibro(libro){
                this.load = true;
                let form = { libro_id: libro.id };
                axios.put('/libro/inactivar', form).then(response => {
                    this.messageAlert('center', 'success', 'El libro se elimino correctamente.', null, 'reload');
                    this.load = false;
                }).catch(error => {
                    this.load = false;
                });
            },
            addDefectuosos(libro){
                this.form.id = libro.id;
                this.form.libro = libro.titulo;
                this.form.piezas = libro.piezas;
                this.form.defectuosos = 0;
                this.form.motivo = null;
                this.$bvModal.show('modal-defectuosos');
            },
            saveDefectuosos(defectuosos){
                if(defectuosos.defectuosos <= this.form.piezas){
                    if(defectuosos.motivo.length > 5){
                        this.load = true;
                        this.form.defectuosos = defectuosos.defectuosos;
                        this.form.motivo = defectuosos.motivo;
                        axios.put('/libro/save_defectuosos', this.form).then(response => {
                            this.messageAlert('center', 'success', 'Los libros defectuosos se guardaron correctamente.', null, 'reload');
                            this.load = false;
                        }).catch(error => {
                            this.load = false;
                        });
                    } else {
                        this.makeToast('warning', 'El motivo debe contener mínimo 5 caracteres.');
                    }
                } else {
                    this.makeToast('warning', 'El número de piezas defectuosas es mayor a las piezas en existencia');
                }
            }
        }
    }
</script>