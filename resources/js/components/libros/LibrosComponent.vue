<template>
    <div>
        <check-connection-component></check-connection-component>
        <div class="row">
            <div class="col-md-4">
                <!-- BUSCAR LIBRO POR TITULO -->
                <b-row>
                    <b-col sm="2"><label>Titulo</label></b-col>
                    <b-col sm="10">
                        <b-input style="text-transform:uppercase;" v-model="queryTitulo"
                            @keyup="http_titulo()"></b-input>
                    </b-col>
                </b-row>
            </div>
            <div class="col-md-4">
                <!-- BUSCAR LIBRO POR ISBN -->
                <b-row>
                    <b-col sm="2">
                        <label>ISBN</label>
                    </b-col>
                    <b-col sm="10">
                        <b-input v-model="isbn" @keyup="http_isbn()">
                        </b-input>
                    </b-col>
                </b-row>
            </div>
            <!-- BUSCAR LIBROS POR EDITORIAL -->
            <div class="col-md-4">
                <b-row>
                    <b-col sm="2">
                        <label for="input-cliente">Editorial</label>
                    </b-col>
                    <b-col sm="10">
                        <b-form-select v-model="queryEditorial" :options="options" @change="http_editorial()">
                        </b-form-select>
                    </b-col>
                </b-row>
            </div>
        </div>
        <hr>
        <b-row class="mb-1">
            <b-col sm="6">
                <!-- PAGINACIÓN -->
                <pagination size="default" :limit="1" :data="librosData" @pagination-change-page="getResults">
                    <span slot="prev-nav"><i class="fa fa-angle-left"></i></span>
                    <span slot="next-nav"><i class="fa fa-angle-right"></i></span>
                </pagination>
            </b-col>
            <b-col sm="2" class="text-right">
                <b-button v-if="role_id === 1 || role_id === 2 || role_id === 3 || role_id == 6" variant="dark" pill
                    block href="/libro/all_sistemas" target="_blank">
                    Sistemas
                </b-button>
            </b-col>
            <b-col sm="2" class="text-right">
                <b-button v-if="role_id === 1 || role_id === 2 || role_id === 3 || role_id == 6" variant="dark" pill
                    block href="/codes/scratch" target="_blank">
                    Scratch
                </b-button>
            </b-col>
            <b-col sm="2" class="text-right">
                <b-button v-if="role_id === 1 || role_id === 2 || role_id == 6" variant="dark" pill block
                    href="/codes/licencias_demos" target="_blank">
                    Licencias / Demos
                </b-button>
            </b-col>
        </b-row>
        <b-row class="mb-2">
            <b-col sm="8"></b-col>
            <b-col sm="2" class="text-right">
                <!-- DESCARGAR LIBROS downloadExcel -->
                <b-button :href="`/downloadExcel/${queryEditorial}`" variant="dark" pill block>
                    <i class="fa fa-download"></i> Descargar
                </b-button>
            </b-col>
            <b-col sm="2" class="text-right">
                <!-- AGREGAR UN NUEVO LIBRO -->
                <b-button v-if="role_id === 1 || role_id === 2 || role_id === 3 || role_id == 6" variant="success" pill
                    block v-b-modal.modal-newLibro>
                    <i class="fa fa-plus"></i> Nuevo libro
                </b-button>
            </b-col>
        </b-row>
        <div v-if="!load">
            <!-- LISTADO DE LIBROS-->
            <b-table v-if="libros.length > 0" responsive :fields="fields" :items="libros">
                <template v-slot:cell(index)="data">
                    {{ data.index + 1 }}
                </template>
                <template v-slot:cell(piezas)="data">
                    {{ data.item.piezas | formatNumber }}
                </template>
                <template v-slot:cell(defectuosos)="data">
                    {{ data.item.defectuosos | formatNumber }}
                </template>
                <template v-slot:cell(accion)="data">
                    <b-button v-if="(role_id == 6 || role_id == 1) && data.item.externo == false" style="color:white;"
                        variant="warning" pill size="sm" v-b-modal.modal-editar
                        @click="editarLibro(data.item, data.index)">
                        <i class="fa fa-pencil"></i>
                    </b-button>
                    <div>
                        <b-button v-if="role_id == 6 || role_id == 1" variant="danger" pill
                            @click="inactivarLibro(data.item)" size="sm">
                            <i class="fa fa-close"></i>
                        </b-button>
                        <b-button v-if="(role_id == 6 || role_id == 1 || role_id == 10) && (data.item.piezas > 0)"
                            variant="secondary" pill @click="addDefectuosos(data.item)" size="sm">
                            <i class="fa fa-minus"></i>
                        </b-button>
                    </div>
                </template>
            </b-table>
            <b-alert v-else show variant="secondary">
                <i class="fa fa-warning"></i> No se encontraron registros.
            </b-alert>
            <!-- MODAL PARA EDITAR UN LIBRO -->
            <b-modal id="modal-editar" title="Editar libro">
                <editar-libro-component :formlibro="formlibro" :listEditoriales="listEditoriales"
                    @actualizarLibro="libroModificado"></editar-libro-component>
                <div slot="modal-footer"></div>
            </b-modal>
            <!-- MODAL PARA AGREGAR DEFECTUOSOS -->
            <b-modal id="modal-defectuosos" :title="form.libro" hide-footer size="sm">
                <add-defectuosos-component @saveDefectuosos="saveDefectuosos"></add-defectuosos-component>
            </b-modal>
        </div>
        <div v-else class="text-center text-info my-2 mt-3">
            <b-spinner class="align-middle"></b-spinner>
            <strong>Cargando...</strong>
        </div>

        <!-- MODAL PARA AGREGAR UN LIBRO -->
        <b-modal id="modal-newLibro" title="Nuevo libro">
            <new-libro-component @actualizarLista="actLista" :listEditoriales="listEditoriales"></new-libro-component>
            <div slot="modal-footer"></div>
        </b-modal>
        <!-- MODAL DE AYUDA GRAL-->
        <b-modal id="modal-ayudaL" hide-backdrop hide-footer title="Ayuda">
            <h5 id="titleA"><b>Búsqueda por titulo</b></h5>
            <p>Escribir el título del libro y aparecerán las coincidencias conforme vaya escribiendo.</p>
            <h5 id="titleA"><b>Búsqueda por ISBN</b></h5>
            <p>Escribir el ISBN (completo) y presionar <label id="ctrlS">Enter</label>.</p>
            <h5 id="titleA"><b>Búsqueda por editorial</b></h5>
            <p>Elegir la editorial que desee y aparecerán todos los libros relacionados a esta.</p>
            <h5 id="titleA"><b>Descargar lista completa</b></h5>
            <p>
                Si la opción de TODOS LOS LIBROS esta activa en la búsqueda por editorial, se descargará la lista
                completa de libros en formato EXCEL.
            </p>
            <h5 id="titleA"><b>Descargar lista por editorial</b></h5>
            <p>
                Si alguna editorial esta activa en la búsqueda por editorial se descargará la lista de libros
                relacionados a esta en formato EXCEL.
            </p>
            <div v-if="role_id === 3">
                <h5 id="titleA"><b>Nuevo libro</b></h5>
                <p>Puede agregar un libro proporcionando el Titulo, ISBN, Autor y Editorial.</p>
                <h5 id="titleA"><b>Editar libro</b></h5>
                <p>Puede modificar Titulo, ISBN, Autor o Editorial de cualquier libro.</p>
                <p>
                    <b><i class="fa fa-info-circle"></i> Nota:</b>
                <ul>
                    <li>Todos los campos son obligatorios, excepto el autor.</li>
                    <li>El titulo e ISBN son únicos, es decir no se pueden agregar los mismos datos de un libro ya
                        existente.</li>
                </ul>
                </p>
            </div>
        </b-modal>
    </div>
</template>

<script>
import sweetAlert from '../../mixins/sweetAlert';
import AddDefectuososComponent from './AddDefectuososComponent.vue';
    export default {
        components: { AddDefectuososComponent },
        props: ['role_id', 'editoriales'],
        mixins: [sweetAlert],
        data() {
            return {
                formlibro: {},
                librosData: {},
                libros: [],
                errors: {},
                posicion: 0,
                perPage: 10,
                loaded: false,
                success: false,
                currentPage: 1,
                queryTitulo: '',
                queryEditorial: 'TODO',
                fields: [
                    { key: 'index', label: 'N.' },
                    'editorial',
                    'ISBN', 
                    'titulo', 
                    { key: 'type', label: 'Tipo' },
                    'piezas',
                    { key: 'scratch', label: 'Scratch' },
                    { key: 'count_solo', label: 'Digital / Físico' },
                    'defectuosos',
                    {key:'accion', label:''}
                ],
                options: [],
                listEditoriales: [],
                loadRegisters: false,
                isbn: '',
                libro: {},
                sTLibro: false,
                sTIsbn: false,
                sEditorial: false,
                form: {
                    id: null,
                    libro: null,
                    defectuosos: 0,
                    motivo: null
                },
                load: true
            }
        },
        created: function(){
            this.getResults();
            this.assign_editorial();
        },
        filters: {
            formatNumber: function (value) {
                return numeral(value).format("0,0[.]00"); 
            }
        },
        methods: {
            getResults(page = 1){
                if(!this.sTLibro && !this.sTIsbn && !this.sTEditorial)
                    this.http_libros(page);
                if(this.sTLibro) 
                    this.http_titulo(page);
                if(this.sTIsbn)
                    this.http_isbn(page);
                if(this.sTEditorial)
                    this.http_editorial(page);
            },
            // HTTP REMCLIENTE
            http_libros(page = 1){
                this.load = true;
                axios.get(`/libro/index?page=${page}`).then(response => {
                    this.assign_values(response.data, false, false, false);
                    this.load = false;   
                }).catch(error => {
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                    this.load = false;
                });
            },
            // HTTP LIBROS
            http_titulo(page = 1){
                this.load = true;
                axios.get(`/libro/by_titulo?page=${page}`, {params: {titulo: this.queryTitulo}}).then(response => {
                    this.assign_values(response.data, true, false, false);
                    this.load = false;
                }).catch(error => {
                    this.load = false;
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                });
            },
            // BUSCAR LIBRO POR ISBN
            http_isbn(page = 1) {
                this.load = true;
                axios.get(`/libro/by_isbn?page=${page}`, {params: {isbn: this.isbn}}).then(response => {
                    this.assign_values(response.data, false, true, false);
                    this.load = false;
                }).catch(error => {
                    this.load = false;
                    this.makeToast('danger', 'ISBN incorrecto');
                });
            },
            // MOSTRAR LIBROS POR EDITORIAL
            http_editorial(page = 1){
                this.load = true;
                axios.get(`/libro/by_editorial?page=${page}`, {params: {editorial: this.queryEditorial}}).then(response => {
                    this.assign_values(response.data, false, false, true);
                    this.load = false;
                }).catch(error => {
                    this.load = false;
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                });
            },
            assign_values(response, sTLibro, sTIsbn, sTEditorial){
                this.librosData = response.paginate;
                this.libros = response.libros;
                this.sTLibro = sTLibro;
                this.sTIsbn = sTIsbn;
                this.sTEditorial = sTEditorial;
            },
            // MOSTRAR LIBROS POR COINCIDENCIA DE TITULO
            assign_editorial(){
                this.options.push({
                    value: 'TODO',
                    text: 'Seleccionar una opción', disabled: true
                });
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
            // INICIALIZAR PARA EDITAR LIBRO
            editarLibro(libro, i){
                this.formlibro = libro;
                this.posicion = i;
            },
            // AGREGAR LIBRO AL LISTADO (EVENTO)
            actLista(libro){
                this.libros.unshift(libro);
                this.$bvModal.hide('modal-newLibro');
                this.makeToast('success', 'El libro de agrego correctamente.');
            },
            libroModificado(libro){
                this.$bvModal.hide('modal-editar');
                this.libros[this.posicion].type = libro.type;
                this.libros[this.posicion].ISBN = libro.ISBN;
                this.libros[this.posicion].titulo = libro.titulo;
                this.libros[this.posicion].editorial = libro.editorial;
                this.makeToast('success', 'El libro se modifico correctamente.');
            },
            // ELIMINAR LIBRO (FUNCIÓN NO UTILIZADA)
            eliminarLibro(){
                axios.delete('/eliminar_libro', {params: {id: this.formlibro.id}}).then(response => {
                    this.$bvModal.hide('modal-eliminar');
                })
                .catch(error => {
                    this.loaded = false;
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                });
            },
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