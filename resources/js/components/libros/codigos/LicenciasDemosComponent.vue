<template>
    <div>
        <b-row>
            <b-col>
                <!-- BUSQUEDA POR LIBRO -->
                <b-form-group label="Libro"  label-class="font-weight-bold">
                    <b-form-input style="text-transform:uppercase;" 
                        @keyup="mostrar_libros('digital')" v-model="queryTitulo"
                        :disabled="load"
                    ></b-form-input>
                    <div class="list-group" v-if="resultsLibros.length" id="listaL">
                        <a class="list-group-item list-group-item-action" 
                            v-for="(libro, i) in resultsLibros" v-bind:key="i"
                            href="#" @click="get_bylibro(libro)">
                            {{ libro.titulo }}
                        </a>
                    </div>
                </b-form-group>
            </b-col>
            <b-col>
                <!-- BUSQUEDA POR TIPO -->
                <b-form-group label="Tipo"  label-class="font-weight-bold">
                    <b-form-select v-model="queryTipo" :disabled="load"
                        :options="options" @change="get_bytipo()"></b-form-select>
                </b-form-group>
            </b-col>
            <b-col sm="2">
                <!-- DESCARGAR LIBROS -->
                <b-button class="mt-4" variant="dark" pill block :href="`/libro/download/list_claves/${this.queryTipo}`">
                    <i class="fa fa-download"></i> Descargar
                </b-button>
            </b-col>
            <b-col sm="2" v-if="role_id == 6 || role_id == 1">
                <b-button class="mt-4" pill block variant="success" :disabled="load" @click="addClave()">
                    <i class="fa fa-plus-circle"></i> Nuevo libro
                </b-button>
            </b-col>
        </b-row>
        <vue-good-table v-if="!load" :columns="fields" :rows="dataClaves.data" :line-numbers="true" theme="polar-bear" styleClass="vgt-table condensed"
            :totalRows="dataClaves.total"
            :pagination-options="{
                enabled: true,
                mode: 'remote',
                perPage: dataClaves.per_page,
                setCurrentPage: dataClaves.current_page,
                perPageDropdownEnabled: false,
            }"
            @on-page-change="onPageChange"></vue-good-table>
        <load-component v-else></load-component>
        <!-- MODAL AGREGAR CLAVE -->
         <b-modal ref="modal-addClave" title="Licencia / Demo" hide-footer>
            <form @submit.prevent="onSubmit">
                <busqueda-libros text="Libro" :type="'digital'" :results="resultsDigitales"
                    @searchLibros="searchLibros" @libroSelect="libroSelect"></busqueda-libros>
                <b-form-group label="Tipo">
                    <b-form-select v-model="form.tipo" :options="options" required :disabled="load"></b-form-select>
                </b-form-group>
                <b-button :disabled="load" variant="success" type="submit" pill>
                    <i v-if="!load" class="fa fa-plus-circle"></i>
                    <b-spinner v-else type="grow" small></b-spinner>
                    {{ !load ? 'Guardar' : 'Cargando' }}
                </b-button>
            </form>
        </b-modal>
    </div>
</template>

<script>
    import getLibrosType from '../../../mixins/libros/getLibrosType';
    import LoadComponent from '../../funciones/LoadComponent.vue';
    import BusquedaLibros from '../partials/BusquedaLibros.vue';
    import sweetAlert from '../../../mixins/sweetAlert';
    import toast from '../../../mixins/toast';
    export default {
        props: ['role_id'],
        mixins: [getLibrosType, toast, sweetAlert],
        components: {LoadComponent, BusquedaLibros},
        data(){
            return {
                load: false,
                queryTipo: null,
                options: [
                    { value: null, text: 'Selecciona una opción', disabled: true },
                    { value: 'profesor', text: 'profesor' },
                    { value: 'demo', text: 'demo' },
                ],
                fields: [
                    {field: 'titulo', label: 'Libro'},
                    {field: 'tipo', label: 'Tipo'},
                    {field: 'piezas', label: 'Piezas', type: 'number'}
                ],
                dataClaves: {},
                libro_id: null,
                resultsDigitales: [],
                form: {
                    libro_id: null,
                    tipo: null
                }
            }
        },
        created: function(){
            this.getResults();
        },
        methods: {
            // OBTENER RESULTADOS DE CLAVE
            getResults(page = 1){
                if(this.libro_id == null && this.queryTipo == null) this.http_libros(page);
                if(this.libro_id != null) this.http_bylibro(page);
                if(this.queryTipo != null) this.http_bytipo(page);
            },
            // OBTENER TODOS LOS LIBROS
            http_libros(page = 1){
                this.load = true;
                axios.get(`/codes/claves/all?page=${page}`).then(response => {
                    this.dataClaves = response.data;
                    this.load = false;   
                }).catch(error => {
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                    this.load = false;
                });
            },
            // BUSCAR LIBROS SELECCIONADOS
            get_bylibro(libro){
                this.assign_values(libro.id, libro.titulo, null);
                this.http_bylibro();
            },
            // OBTENER LIBRO(S) SELECCIONADOS
            http_bylibro(page = 1){
                this.load = true;
                axios.get(`/codes/claves/by_book?page=${page}`, {params: {libro_id: this.libro_id}}).then(response => {
                    this.dataClaves = response.data;
                    this.load = false;   
                }).catch(error => {
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                    this.load = false;
                });
            },
            // BUSCAR POR TIPO SELECCIONADO
            get_bytipo(){
                this.assign_values(null, null, this.queryTipo);
                this.http_bytipo();
            },
            // OBTENER LIBROS POR TIPO
            http_bytipo(page = 1){
                this.load = true;
                axios.get(`/codes/claves/by_tipo?page=${page}`, {params: {tipo: this.queryTipo}}).then(response => {
                    this.dataClaves = response.data;
                    this.load = false;   
                }).catch(error => {
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                    this.load = false;
                });
            },
            // ASIGNAR VALORES PARA BUSQUEDA
            assign_values(libro_id, titulo, queryTipo){
                this.libro_id = libro_id;
                this.queryTitulo = titulo;
                this.queryTipo = queryTipo;
                this.resultsLibros = [];
            },
            // AGREGAR LIBRO A INVENTARIO DE CLAVES
            addClave(){
                this.assign_datos(null, null);
                this.$refs['modal-addClave'].show();
            },
            // CAMBIAR DE PAGINA Y OBTENER DATOS
            onPageChange(params){
                this.getResults(params.currentPage);
            },
            // BUSCAR LIBROS
            searchLibros(query, type) {
                if(query.length > 3){
                    axios.get(`/libro/by_editorial_digital`, { params: { titulo: query, editorial: 'MAJESTIC EDUCATION', type: type } }).then(response => {
                        this.resultsDigitales = response.data;
                    }).catch(error => {
                        this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                    });
                } else {
                    this.resultsDigitales = [];
                }
            },
            // LIBRO SELECCIONADO
            libroSelect(libro) {
                this.assign_datos(libro.id, null);
            },
            // ASIGNAR DATOS DEL LIBRO SELECCIONADO
            assign_datos(libro_id, tipo){
                this.form.libro_id = libro_id;
                this.form.tipo = tipo;
                this.resultsDigitales = [];
            },
            // GUARDAR CLAVE
            onSubmit(){
                this.load = true;
                axios.post('/codes/claves/store', this.form).then(response => {
                    if (response.data){
                        this.$refs['modal-addClave'].hide();
                        this.messageAlert('center', 'success', `Licencia / Demo se guardo correctamente`, null, 'reload');
                    } else
                        this.messageAlert('center', 'warning', 'Licencia / Demo seleccionado ya existe', null, 'info');
                    this.load = false;
                }).catch(error => {
                    this.load = false;
                });
            }
        }
    }
</script>