<template>
    <div>
        <div v-if="!showAddEntrada">
            <b-row class="mb-2">
                <b-col>
                    <!-- BUSQUEDA POR EDITORIAL -->
                    <b-form-group label="Editorial:">
                        <b-form-select v-model="queryEditorial" :disabled="load"
                            :options="options" @change="get_byeditorial()"></b-form-select>
                    </b-form-group>
                </b-col>
                <b-col>
                    <!-- BUSQUEDA POR LIBRO -->
                    <b-form-group label="Libro:">
                        <b-form-input style="text-transform:uppercase;" 
                            @keyup="mostrar_libros()" v-model="queryTitulo"
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
                <!-- BUSQUEDA POR CLIENTE -->
                <!-- <b-col>
                    <b-form-group label="Cliente:">
                        <b-form-input style="text-transform:uppercase;" :disabled="load"
                            v-model="queryCliente" @keyup="mostrarClientes()">
                        </b-form-input>
                        <div class="list-group" v-if="clientes.length" id="listaL">
                            <a v-for="(cliente, i) in clientes" v-bind:key="i"
                                href="#" class="list-group-item list-group-item-action" 
                                @click="get_bycliente(cliente)">
                                {{ cliente.name }}
                            </a>
                        </div>
                    </b-form-group>
                </b-col> -->
                <b-col>
                    <!-- BUSCAR POR CODIGO -->
                    <b-form-group label="Código:">
                        <b-form-input style="text-transform:uppercase;" 
                            v-model="queryCode" @keyup="mostrar_codes()"
                        ></b-form-input>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col>
                    <!-- PAGINACIÓN -->
                    <pagination size="default" :limit="1" :data="codesData" 
                        @pagination-change-page="get_results">
                        <span slot="prev-nav"><i class="fa fa-angle-left"></i></span>
                        <span slot="next-nav"><i class="fa fa-angle-right"></i></span>
                    </pagination>
                </b-col>
                <!-- <b-col sm="2" class="text-right">
                    <b-button variant="dark" pill block :href="`/codes/download/`">
                        <i class="fa fa-download"></i> Descargar
                    </b-button>
                </b-col> -->
                <b-col sm="2" class="text-right">
                    <!-- CARGAR CODIGOS -->
                    <!-- v-if="role_id === 1 || role_id === 2 || role_id == 6" -->
                    <b-button v-if="role_id !== 3" variant="success" pill 
                        block @click="showAddEntrada = !showAddEntrada">
                        <i class="fa fa-plus-circle"></i> Crear entrada
                    </b-button>
                </b-col>
            </b-row>
            <b-table v-if="!load" :items="codes" :fields="fields" responsive>
                <template v-slot:cell(index)="data">
                    {{ data.index + 1 }}
                </template>
                <template v-slot:cell(estado)="data">
                    <b-badge v-if="data.item.estado == 'inventario'" variant="primary">Disponible</b-badge>
                    <b-badge v-if="data.item.estado == 'ocupado'" variant="danger">Ocupado</b-badge>
                </template>
                <template v-slot:cell(clientes)="row">
                    <b-button variant="info" pill block size="sm" 
                        @click="show_remisiones(row.item)">
                        Mostrar
                    </b-button>
                </template>
            </b-table>
            <load-component v-else></load-component>
        </div>
        <div v-else>
            <b-row class="mb-3">
                <b-col sm="2">
                    <b-button @click="showAddEntrada = !showAddEntrada" 
                        variant="secondary" pill>
                        <i class="fa fa-arrow-left"></i> Regresar
                    </b-button>
                </b-col>
                <b-col><h5><b>Crear entrada</b></h5></b-col>
            </b-row>
            <entrada-codes-component></entrada-codes-component>
        </div>
        <!-- MODALS -->
        <b-modal id="modal-showRemisiones" :title="`${detailsCode.libro}`" hide-footer>
            <h6><b>Código: </b>{{ detailsCode.codigo }}</h6>
            <b-table v-if="detailsCode.remisiones.length > 0"
                :items="detailsCode.remisiones" :fields="fieldsRem">
                <template v-slot:cell(index)="data">
                    {{ data.index + 1 }}
                </template>
                <template v-slot:cell(id)="data">
                    <a :href="`/remisiones/details/${data.item.id}`" target="blank">
                        {{ data.item.id }}
                    </a>
                </template>
            </b-table>
            <b-alert v-else show variant="secondary">
                <i class="fa fa-warning"></i> No se encontraron remisiones.
            </b-alert>
        </b-modal>
    </div>
</template>

<script>
import getEditoriales from '../../../mixins/getEditoriales';
import searchCliente from '../../../mixins/searchCliente';
import LoadComponent from '../../cortes/partials/LoadComponent.vue';
export default {
    props: ['role_id'],
  components: { LoadComponent },
    mixins: [getEditoriales, searchCliente],
    data(){
        return {
            load: false,
            codesData: {},
            codes: [],
            fields: [
                {key:'index', label:'N.'},
                {key:'libro.titulo', label:'Libro'},
                {key:'codigo', label:'Código'},
                'tipo',
                {key:'created_at', label:'Se subio el'},
                'estado', 'clientes'
            ],
            showAddEntrada: false,
            queryTitulo: null,
            resultsLibros: [],
            libro_id: null,
            queryEditorial: null,
            cliente_id: null,
            queryCode: null,
            detailsCode: {
                libro: null,
                codigo: null,
                remisiones: []
            },
            fieldsRem: [
                {key:'index', label:'N.'},
                {key:'id', label:'Folio'},
                {key:'cliente.name', label: 'Cliente'}
            ]
        }
    },
    created: function(){
        this.get_editoriales();
        this.get_results();
    },
    methods: {
        // OBTENER LOS RESULTADOS PAGINADOS
        get_results(page = 1){
            if(this.libro_id == null && this.queryEditorial == null && this.queryCode == null)
                this.http_libros(page);
            if(this.libro_id !== null) this.http_bylibro(page);
            if(this.queryEditorial !== null) this.http_byeditorial(page);
            if(this.queryCode !== null) this.http_bycode(page);
            // if(this.cliente_id !== null) this.http_bycliente(page);
        },
        // OBTENER TODOS LOS CODIGOS
        http_libros(page = 1){
            this.load = true;
            axios.get(`/codes/index?page=${page}`).then(response => {
                this.codesData = response.data; 
                this.codes = response.data.data;
                this.load = false;   
            }).catch(error => {
                this.load = false;
            });
        },
        // MOSTRAR LISTA DE LIBROS DIGITALES
        mostrar_libros(){
            if(this.queryTitulo.length > 0){
                axios.get('/libro/by_type', {params: {titulo: this.queryTitulo}}).then(response => {
                    this.resultsLibros = response.data;
                }).catch(error => {
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                });
            } else{
                this.resultsLibros = [];
            }
        },
        // OBTENER CODIGOS POR LIBRO
        get_bylibro(libro){
            this.libro_id = libro.id;
            this.queryTitulo = libro.titulo;
            this.resultsLibros = [];
            this.http_bylibro();
            this.queryEditorial = null;
            this.queryCode = null;
            // this.cliente_id = null;
            // this.queryCliente = null;
        },
        // HTTP DE CODIGOS
        http_bylibro(page = 1){
            this.load = true;
            axios.get(`/codes/by_libro?page=${page}`, {params: {libro_id: this.libro_id}}).then(response => {
                this.codesData = response.data; 
                this.codes = response.data.data;
                this.load = false;
            }).catch(error => {
                this.load = false;
                this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
            });
        },
        // OBTENER POR EDITORIAL
        get_byeditorial(){
            this.http_byeditorial();
            this.libro_id = null;
            this.queryTitulo = null;
            this.queryCode = null;
            // this.cliente_id = null;
            // this.queryCliente = null;
        },
        http_byeditorial(page = 1){
            this.load = true;
            axios.get(`/codes/by_editorial?page=${page}`, {params: {editorial: this.queryEditorial}}).then(response => {
                this.codesData = response.data; 
                this.codes = response.data.data;
                this.load = false;
            }).catch(error => {
                this.load = false;
                this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
            });
        },
        // get_bycliente(cliente){
        //     this.cliente_id = cliente.id;
        //     this.queryCliente = cliente.name;
        //     this.clientes = [];
        //     this.http_bycliente();
        //     this.queryEditorial = null;
        //     this.libro_id = null;
        //     this.queryTitulo = null;
        // },
        // http_bycliente(page = 1){
        //     this.load = true;
        //     axios.get(`/codes/by_cliente?page=${page}`, {params: {cliente_id: this.cliente_id}}).then(response => {
        //         this.codesData = response.data; 
        //         this.codes = response.data.data;
        //         this.load = false;
        //     }).catch(error => {
        //         this.load = false;
        //         this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
        //     });
        // },
        mostrar_codes(){
            this.queryEditorial = null;
            this.libro_id = null;
            this.queryTitulo = null;
            this.http_bycode();
        },
        http_bycode(page = 1){
            this.load = true;
            axios.get(`/codes/by_code?page=${page}`, {params: {code: this.queryCode}}).then(response => {
                this.codesData = response.data; 
                this.codes = response.data.data;
                this.load = false;   
            }).catch(error => {
                this.load = false;
            });
        },
        show_remisiones(codigo){
            this.load = true;
            this.detailsCode = {
                libro: null,
                codigo: null,
                remisiones: []
            };
            axios.get('/codes/show_remisiones', {params: {code_id: codigo.id}}).then(response => {
                this.detailsCode.libro = codigo.libro.titulo;
                this.detailsCode.codigo = codigo.codigo;
                this.detailsCode.remisiones = response.data;
                this.$bvModal.show('modal-showRemisiones')
                this.load = false;   
            }).catch(error => {
                this.load = false;
            });
        }
    }
}
</script>

<style>

</style>