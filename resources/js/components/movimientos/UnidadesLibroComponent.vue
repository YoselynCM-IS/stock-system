<template>
    <div>
        <b-row>
            <b-col>
                <b-row>
                    <b-col sm="1"><label>Buscar</label></b-col>
                    <b-col sm="7">
                        <b-input
                            style="text-transform:uppercase;"
                            v-model="queryTitulo" autofocus
                            @keyup="mostrarLibros()"
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
                </b-row>
            </b-col>
            <b-col sm="3">
                <b-button variant="dark" href="/administrador/download_ulibros">
                    <i class="fa fa-download"></i> Descargar <br>
                    <i style="font-size: 12px;">Todo</i>
                </b-button>
            </b-col>
            <b-col sm="2" class="text-right">
                <b-button v-if="viewDetails" @click="viewDetails = !viewDetails" variant="dark">
                    <i class="fa fa-arrow-left"></i> Volver
                </b-button>
            </b-col>
        </b-row><br>
        <div v-if="!viewDetails">
            <b-table :items="libros" :fields="fieldsLibros">
                <template v-slot:cell(details)="row">
                    <b-button variant="info" v-on:click="showDetails(row.item)">Mostrar</b-button>
                </template>
            </b-table>
        </div>
        <div v-else>
            <h6><b>Libro: </b> {{ libro.titulo }}</h6><br>
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
                libros: [],
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
                queryTitulo: ''
            }
        },
        created: function(){
            axios.get('/administrador/getULibros').then(response => {
                this.libros = response.data;
            }); 
        },
        methods: {
            showDetails(libro){
                axios.get('/administrador/detallesULibro', {params: {libro_id: libro.libro_id}}).then(response => {
                    this.libro.titulo = libro.libro;
                    this.libro.unidades_vendidas = libro.unidades_vendidas;
                    this.libro.unidades_remisiones = libro.unidades_remisiones;
                    this.libro.unidades_devoluciones = libro.unidades_devoluciones;
                    this.libro.registros = response.data;
                    this.viewDetails = true;
                });
            },
            mostrarLibros(){
                this.getLibros(this.queryTitulo);
            },
            obtenerLibro(libro){
                axios.get('/administrador/detallesULibro', {params: {libro_id: libro.id}}).then(response => {
                    if(response.data.length > 0){
                        this.libro.titulo = libro.titulo;
                        this.libro.registros = response.data;
                        this.viewDetails = true;
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
            }
        }
    }
</script>
