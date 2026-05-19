<template>
    <div>
        <div v-if="!viewDetails">
            <b-row>
                <b-col sm="3">
                    <b-input
                        style="text-transform:uppercase;"
                        v-model="queryTitulo" autofocus
                        @keyup="mostrarLibros()"
                        placeholder="Buscar libro..."
                    ></b-input>
                    <div class="list-group" v-if="resultslibros.length" id="listaL">
                        <a 
                            class="list-group-item list-group-item-action" 
                            href="#" 
                            v-bind:key="i" 
                            v-for="(libro, i) in resultslibros" 
                            @click="obtenerLibro(libro)">
                            {{ libro.titulo }}
                        </a>
                    </div>
                </b-col>
                <b-col sm="3">
                    <b-row>
                        <b-col sm="1">De:</b-col>
                        <b-col>
                            <input class="form-control" type="date" v-model="fechas.de">
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col sm="1">A:</b-col>
                        <b-col>
                            <input class="form-control" type="date" v-model="fechas.a" @change="http_fechas()">
                        </b-col>
                    </b-row>
                </b-col>
                <b-col sm="2">
                    <b-button variant="dark" :href="'/administrador/download_ulibros' + (fechas.de && fechas.a ? '?inicio=' + fechas.de + '&final=' + fechas.a : '')">
                        <i class="fa fa-download"></i> Descargar
                    </b-button>
                </b-col>
                <b-col sm="4" class="text-right">
                    <!-- PAGINACIÓN -->
                    <pagination size="default" :limit="1" :data="libros"
                        @pagination-change-page="getResults">
                        <span slot="prev-nav"><i class="fa fa-angle-left"></i></span>
                        <span slot="next-nav"><i class="fa fa-angle-right"></i></span>
                    </pagination>
                </b-col>
            </b-row><br>
        
            <b-table :items="libros.data" :fields="fieldsLibros">
                <template v-slot:cell(details)="row">
                    <b-button variant="info" v-on:click="showDetails(row.item)">Mostrar</b-button>
                </template>
            </b-table>
        </div>
        <div v-else>
            <b-row>
                <b-col><h6><b>Libro: </b> {{ libro.titulo }}</h6></b-col>
                <b-col sm="2">
                    <b-button v-if="viewDetails" @click="viewDetails = !viewDetails" variant="dark">
                        <i class="fa fa-arrow-left"></i> Volver
                    </b-button>
                </b-col>
            </b-row>
            <b-table :items="libro.registros" :fields="fieldsDetails">
                <template v-slot:cell(index)="row">{{ row.index + 1 }}</template>
                <template #thead-top="row">
                    <tr>
                        <th colspan="2"></th>
                        <th>{{ libro.unidades_vendidas }}</th>
                        <th>{{ libro.unidades_remisiones }}</th>
                        <th>{{ libro.unidades_devoluciones }}</th>
                    </tr>
                </template>
            </b-table>
        </div>
    </div>
</template>

<script>
    import getLibros from './../../mixins/getLibros';
    export default {
        mixins: [getLibros],
        data() {
            return {
                libros: {},
                fieldsLibros: [
                    'libro',
                    { key: 'unidades_vendidas', label: 'Unidades (Vendidas)', variant: 'success', sortable: true },
                    { key: 'unidades_remisiones', label: 'Unidades (Salida)' },
                    { key: 'unidades_devoluciones', label: 'Unidades (Devoluciones)' },
                    { key: 'details', label: 'Detalles' }
                ],
                fieldsDetails: [
                    { key: 'index', label: '' }, 'cliente',
                    { key: 'unidades_vendidas', label: 'Unidades (Vendidas)', variant: 'success' },
                    { key: 'unidades_remisiones', label: 'Unidades (Salida)' },
                    { key: 'unidades_devoluciones', label: 'Unidades (Devoluciones)' }
                ],
                libro: {
                    titulo: '',
                    unidades_vendidas: 0,
                    unidades_remisiones: 0,
                    unidades_devoluciones: 0,
                    registros: []
                },
                viewDetails: false,
                queryTitulo: '',
                fechas: {
                    de: null,
                    a: null
                }
            }
        },
        created: function(){
            this.getResults();
        },
        methods: {
            // OBTENER RESULTADOS
            getResults(page = 1){
                if(this.fechas.a == null) this.http_all(page);
                else this.http_fechas(page);
            },
            // OBTENER TODOS LOS REGISTROS
            http_all(page = 1){
                axios.get(`/administrador/getULibros?page=${page}`).then(response => {
                    this.libros = response.data;
                }); 
            },
            // OBTENER MOVIMIENTOS POR FECHA
            http_fechas(page = 1){
                if(this.fechas.de !== null){
                    axios.get(`/administrador/byFechaULibros?page=${page}`, {params: {inicio: this.fechas.de, final: this.fechas.a}}).then(response => {
                        this.libros = response.data;
                    });
                } 
            },
            showDetails(libro){
                axios.get('/administrador/detallesULibro', {params: {libro_id: libro.libro_id, inicio: this.fechas.de, final: this.fechas.a}}).then(response => {
                    this.libro.titulo = libro.libro;
                    this.set_information(response);
                });
            },
            mostrarLibros(){
                this.getLibros(this.queryTitulo);
            },
            obtenerLibro(libro){
                axios.get('/administrador/detallesULibro', {params: {libro_id: libro.id, inicio: this.fechas.de, final: this.fechas.a}}).then(response => {
                    if(response.data.detalles.length > 0){
                        this.libro.titulo = libro.titulo;
                        this.set_information(response);
                    } else {
                        this.$bvToast.toast(`${libro.titulo} no cuenta con registro de remisiones`, {
                            title: 'Mensaje',
                            variant: 'warning',
                            solid: true
                        });
                    }
                    this.queryTitulo = '';
                    this.resultslibros = [];
                }); 
            },
            set_information(response){
                this.libro.unidades_vendidas = response.data.totales.total_vendidas;
                this.libro.unidades_remisiones = response.data.totales.total_remisiones;
                this.libro.unidades_devoluciones = response.data.totales.total_devoluciones;
                this.libro.registros = response.data.detalles;
                this.viewDetails = true;
            }
        }
    }
</script>
