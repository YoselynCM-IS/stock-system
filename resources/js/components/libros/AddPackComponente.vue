<template>
    <div>
        <b-row>
            <b-col>
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
            </b-col>
            <b-col>
                <!-- BUSCAR LIBRO POR TITULO -->
                <b-form-group label="Libro" label-class="font-weight-bold">
                    <b-input style="text-transform:uppercase;" v-model="queryLibro" @keyup="getLibros(queryLibro)"></b-input>
                    <div class="list-group" v-if="resultslibros.length" id="listaL">
                        <a class="list-group-item list-group-item-action" href="#"
                            v-for="(libro, i) in resultslibros" v-bind:key="i" @click="byBook(libro)">
                            {{ libro.titulo }}
                        </a>
                    </div>
                </b-form-group>
            </b-col>
            <b-col sm="2">
                <!-- DESCARGAR LIBROS -->
                <b-button class="mt-4" variant="dark" pill block href="/libro/download/list_scratch">
                    <i class="fa fa-download"></i> Descargar
                </b-button>
            </b-col>
            <b-col sm="2" v-if="role_id == 6 || role_id == 1">
                <b-button class="mt-4" variant="success" pill block @click="addPack()" :disabled="load">
                    <i class="fa fa-plus-circle"></i> Nuevo pack
                </b-button>
            </b-col>
        </b-row>
        <vue-good-table v-if="!load" :columns="fields" :rows="dataPacks.data" :line-numbers="true" theme="polar-bear" styleClass="vgt-table condensed"
            :totalRows="dataPacks.total"
            :pagination-options="{
                enabled: true,
                mode: 'remote',
                perPage: dataPacks.per_page,
                setCurrentPage: dataPacks.current_page,
                perPageDropdownEnabled: false,
            }"
            @on-page-change="onPageChange"></vue-good-table>
        <load-component v-else></load-component>
        <b-modal ref="modal-addPack" title="Nuevo pack" hide-footer>
            <form @submit="onSubmit">
                <busqueda-libros text="Libro físico" :type="'venta'" :results="rFisicos"
                    @searchLibros="searchLibros" @libroSelect="libroSelect"></busqueda-libros>
                <busqueda-libros text="Libro digital" :type="'digital'" :results="rDigitales"
                    @searchLibros="searchLibros" @libroSelect="libroSelect"></busqueda-libros>
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
import LoadComponent from '../funciones/LoadComponent.vue';
import BusquedaLibros from './partials/BusquedaLibros.vue';
import sweetAlert from '../../mixins/sweetAlert';
import getLibros from '../../mixins/getLibros';
import getSeries from '../../mixins/libros/getSeries';
export default {
    props: ['role_id'],
    components: { BusquedaLibros, LoadComponent },
    mixins: [sweetAlert, getLibros, getSeries],
    data() {
        return {
            form: {
                libro_fisico: null,
                libro_digital: null
            },
            load: false,
            rDigitales: [],
            rFisicos: [],
            fields: [
                {field: 'serie', label: 'Serie'},
                {field: 'fisico', label: 'Libro físico'},
                {field: 'digital', label: 'Libro digital'},
                {field: 'piezas', label: 'Piezas', type: 'number'}
            ],
            dataPacks: {},
            queryLibro: null,
            libro_id: null,
            serie_id: null
        }
    },
    created: function(){
        this.getResults();
    },
    methods: {
        // OBTENER RESULTADOS DE LIBROS PACK
        getResults(page = 1){
            if(this.libro_id == null && this.serie_id == null) this.http_libros(page);
            if(this.libro_id != null) this.http_bylibro(page);
            if(this.serie_id != null) this.http_byserie(page);
        },
        // CAMBIAR DE PAGINA Y OBTENER DATOS
        onPageChange(params){
            this.getResults(params.currentPage);
        },
        // OBTENER TODOS LOS LIBROS
        http_libros(page = 1){
            this.load = true;
            axios.get(`/libro/scratch/all?page=${page}`).then(response => {
                this.dataPacks = response.data;
                this.load = false;   
            }).catch(error => {
                this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                this.load = false;
            });
        },
        // OBTENER SERIE SELECCIONADA
        bySerie(serie){
            this.assign_values(serie.id, serie.serie, null, null);
            this.http_byserie();
        },
        // OBTENER LIBROS POR SERIE
        http_byserie(page = 1){
            this.load = true;
            axios.get(`/libro/scratch/by_serie?page=${page}`, {params: {serie_id: this.serie_id}}).then(response => {
                this.dataPacks = response.data;
                this.load = false;   
            }).catch(error => {
                this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                this.load = false;
            });
        },
        // OBTENER LIBRO SELECCIONADO
        byBook(libro){
            this.assign_values(null, null, libro.id, libro.titulo);
            this.http_bylibro();
        },
        // OBTENER LIBROS POR TITULO
        http_bylibro(page = 1){
            this.load = true;
            axios.get(`/libro/scratch/by_book?page=${page}`, {params: {libro_id: this.libro_id}}).then(response => {
                this.dataPacks = response.data;
                this.load = false;   
            }).catch(error => {
                this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                this.load = false;
            });
        },
        // ASIGNAR VALORES PARA NO AFECTAR CADA BUSQUEDA
        assign_values(serie_id, querySerie, libro_id, queryLibro){
            this.serie_id = serie_id;
            this.querySerie = querySerie;
            this.libro_id = libro_id;
            this.queryLibro = queryLibro;
            this.resultsSeries = [];
            this.resultslibros = [];
        },
        // AGREGAR PAQUETE
        addPack() {
            this.form.libro_fisico = null;
            this.form.libro_digital = null;
            this.rDigitales = [];
            this.rFisicos = [];
            this.$refs['modal-addPack'].show();
        },
        searchLibros(query, type) {
            if(query.length > 3){
                this.load = true;
                axios.get(`/libro/by_editorial_digital`, { params: { titulo: query, editorial: 'MAJESTIC EDUCATION', type: type } }).then(response => {
                    if (type == 'digital') this.rDigitales = response.data;
                    if (type == 'venta') this.rFisicos = response.data;
                    this.load = false;
                }).catch(error => {
                    this.load = false;
                });
            } else {
                this.rDigitales = [];
                this.rFisicos = [];
            }
        },
        libroSelect(libro) {
            if (libro.type == 'digital') {
                this.form.libro_digital = libro.id;
                this.rDigitales = [];
            }
            if (libro.type == 'venta') {
                this.form.libro_fisico = libro.id;
                this.rFisicos = [];
            }
        },
        onSubmit(e) {
            e.preventDefault();
            this.load = true;
            axios.post('/libro/save_pack', this.form).then(response => {
                if (response.data)
                    this.messageAlert('center', 'success', 'El paquete se guardó correctamente.', null, 'reload');
                else
                    this.messageAlert('center', 'warning', 'El paquete ya existe.', null, 'info');
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