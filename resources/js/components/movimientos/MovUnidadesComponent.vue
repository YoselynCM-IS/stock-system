<template>
    <div>
        <check-connection-component></check-connection-component>
        <div v-if="!view_detalles">
            <b-row>
                <b-col>
                    <b-row>
                        <b-col sm="3" class="text-right"><label>Editorial</label></b-col>
                        <b-col sm="7">
                            <b-form-select v-model="queryEMov" :options="options" @change="editorialMov()"></b-form-select>
                        </b-col>
                    </b-row>
                </b-col>
                <b-col>
                    <b-input style="text-transform:uppercase;"
                        placeholder="BUSCAR LIBRO" autofocus
                        v-model="queryTitulo" @keyup="mostrarLibros()"
                    ></b-input>
                    <div class="list-group" v-if="resultslibros.length" id="listaL">
                        <a class="list-group-item list-group-item-action" 
                            v-for="(libro, i) in resultslibros" v-bind:key="i"
                            href="#" @click="obtenerLibro(libro)">
                            {{ libro.titulo }}
                        </a>
                    </div>
                </b-col>
                <b-col sm="2">
                    <!-- BUSQUEDA POR MES -->
                </b-col>
                <b-col sm="2" class="text-right">
                    <b-button v-if="movimientos.length > 0" variant="dark" pill
                        :href="`/libro/down_movgral/${queryEMov}/1`">
                        <i class="fa fa-download"></i> General
                    </b-button>
                </b-col>
                <b-col sm="2" class="text-right">
                    <b-button v-if="movimientos.length > 0" variant="dark" pill
                        :href="`/libro/down_movgral/${queryEMov}/2`">
                        <i class="fa fa-download"></i> Detallado
                    </b-button>
                </b-col>
            </b-row><br>
            <div v-if="!load">
                <div v-if="movimientos.length > 0">
                    <b-table
                        id="table-mov"
                        striped
                        responsive
                        :per-page="perPage"
                        :current-page="currentPage"
                        :items="movimientos"
                        :fields="fields"
                        :tbody-tr-class="rowClass">
                        <template v-slot:cell(existencia)="row">
                            {{ row.item.existencia | formatNumber }}
                        </template>
                        <template v-slot:cell(entradas)="row">
                            {{ row.item.entradas | formatNumber }}
                        </template>
                        <template v-slot:cell(devoluciones)="row">
                            {{ row.item.devoluciones | formatNumber }}
                        </template>
                        <template v-slot:cell(saldevoluciones)="row">
                            {{ row.item.saldevoluciones | formatNumber }}
                        </template>
                        <template v-slot:cell(prodevoluciones)="row">
                            {{ row.item.prodevoluciones | formatNumber }}
                        </template>
                        <template v-slot:cell(notas)="row">
                            {{ row.item.notas | formatNumber }}
                        </template>
                        <template v-slot:cell(remisiones)="row">
                            {{ row.item.remisiones | formatNumber }}
                        </template>
                        <template v-slot:cell(promociones)="row">
                            {{ row.item.promociones | formatNumber }}
                        </template>
                        <template v-slot:cell(donaciones)="row">
                            {{ row.item.donaciones | formatNumber }}
                        </template>
                        <template v-slot:cell(detalles)="row">
                            <b-button variant="info" @click="onDetalles(row.item)" pill>
                                Detalles
                            </b-button>
                        </template>
                        <template #thead-top="row">
                            <tr>
                                <th colspan="3"></th>
                                <th colspan="4" class="table-success text-center">ENTRADAS</th>
                                <th colspan="8" class="table-primary text-center">SALIDAS</th>
                                <th></th>
                            </tr>
                        </template>
                    </b-table>
                    <b-pagination
                        v-model="currentPage"
                        :total-rows="movimientos.length"
                        :per-page="perPage"
                        aria-controls="table-mov">
                    </b-pagination>
                </div>
                <b-alert v-else show variant="secondary">
                    <i class="fa fa-warning"></i> No se encontraron registros.
                </b-alert>
            </div>
            <load-component v-else></load-component>
        </div>
        <div v-if="view_detalles">
            <b-row>
                <b-col>
                    <h5><b>Libro:</b> {{ all_detalles.titulo }}</h5>
                    <h6><b>Existencia</b>: {{ all_detalles.piezas }}</h6>
                </b-col>
                <b-col class="text-right" sm="2">
                    <b-button variant="secondary" @click="view_detalles = false;">
                        <i class="fa fa-mail-reply"></i> Regresar
                    </b-button>
                </b-col>
            </b-row>
            <hr>
            <div v-if="all_detalles.registros.length > 0">
                <b-row>
                    <b-col sm="6"><h6><b>ENTRADAS</b></h6></b-col>
                    <b-col class="text-right">
                        <b-button variant="dark" size="sm" v-b-toggle.collapse-1>
                            <span class="when-opened"><i class="fa fa-eye-slash"></i> Ocultar</span>
                            <span class="when-closed"><i class="fa fa-eye"></i> Mostrar</span>
                        </b-button>
                    </b-col>
                </b-row><br>
                <b-collapse visible id="collapse-1">
                    <b-table striped hover :items="all_detalles.registros" :fields="fregistros">
                        <template v-slot:cell(unidades)="row">
                            {{ row.item.unidades | formatNumber }}
                        </template>
                        <template v-slot:cell(dato)="row">
                            {{ row.item.entrada.folio }}
                        </template>
                    </b-table>
                </b-collapse>
                <hr>
            </div>
            <div v-if="devoluciones.length > 0">
                <b-row>
                    <b-col sm="6"><h6><b>DEVOLUCIÓN (REMISIONES)</b></h6></b-col>
                    <b-col class="text-right">
                        <b-button variant="dark" size="sm" v-b-toggle.collapse-4>
                            <span class="when-opened"><i class="fa fa-eye-slash"></i> Ocultar</span>
                            <span class="when-closed"><i class="fa fa-eye"></i> Mostrar</span>
                        </b-button>
                    </b-col>
                </b-row><br>
                <b-collapse visible id="collapse-4">
                    <b-table striped hover :items="devoluciones" :fields="fregistros">
                        <template v-slot:cell(unidades)="row">
                            {{ row.item.unidades | formatNumber }}
                        </template>
                        <template v-slot:cell(dato)="row">
                            {{ row.item.remisione.id }}
                        </template>
                    </b-table>
                </b-collapse>
                <hr>
            </div>
            <div v-if="prodevoluciones.length > 0">
                <b-row>
                    <b-col sm="6"><h6><b>DEVOLUCIÓN (PROMOCIONES)</b></h6></b-col>
                    <b-col class="text-right">
                        <b-button variant="dark" size="sm" v-b-toggle.collapse-11>
                            <span class="when-opened"><i class="fa fa-eye-slash"></i> Ocultar</span>
                            <span class="when-closed"><i class="fa fa-eye"></i> Mostrar</span>
                        </b-button>
                    </b-col>
                </b-row><br>
                <b-collapse visible id="collapse-11">
                    <b-table striped hover :items="prodevoluciones"></b-table>
                </b-collapse>
                <hr>
            </div>
            <div v-if="all_detalles.saldevoluciones.length > 0">
                <b-row>
                    <b-col sm="6"><h6><b>DEVOLUCIONES (SALIDAS)</b></h6></b-col>
                    <b-col class="text-right">
                        <b-button variant="dark" size="sm" v-b-toggle.collapse-11>
                            <span class="when-opened"><i class="fa fa-eye-slash"></i> Ocultar</span>
                            <span class="when-closed"><i class="fa fa-eye"></i> Mostrar</span>
                        </b-button>
                    </b-col>
                </b-row><br>
                <b-collapse visible id="collapse-7">
                    <b-table striped hover :items="all_detalles.saldevoluciones" :fields="fregistros">
                        <template v-slot:cell(unidades)="row">
                            {{ row.item.unidades | formatNumber }}
                        </template>
                        <template v-slot:cell(dato)="row">
                            {{ row.item.salida.folio }}
                        </template>
                    </b-table>
                </b-collapse>
            </div>
            <div v-if="salidas.length > 0">
                <b-row>
                    <b-col sm="6"><h6><b>SALIDAS (QUERÉTARO)</b></h6></b-col>
                    <b-col class="text-right">
                        <b-button variant="dark" size="sm" v-b-toggle.collapse-8>
                            <span class="when-opened"><i class="fa fa-eye-slash"></i> Ocultar</span>
                            <span class="when-closed"><i class="fa fa-eye"></i> Mostrar</span>
                        </b-button>
                    </b-col>
                </b-row><br>
                <b-collapse visible id="collapse-8">
                    <b-table striped hover :items="salidas"></b-table>
                </b-collapse>
                <hr>
            </div>
            <div v-if="entdevoluciones.length > 0">
                <b-row>
                    <b-col sm="6"><h6><b>DEVOLUCIÓN (ENTRADAS)</b></h6></b-col>
                    <b-col class="text-right">
                        <b-button variant="dark" size="sm" v-b-toggle.collapse-2>
                            <span class="when-opened"><i class="fa fa-eye-slash"></i> Ocultar</span>
                            <span class="when-closed"><i class="fa fa-eye"></i> Mostrar</span>
                        </b-button>
                    </b-col>
                </b-row><br>
                <b-collapse visible id="collapse-2">
                    <b-table striped hover :items="entdevoluciones" :fields="fregistros">
                        <template v-slot:cell(unidades)="row">
                            {{ row.item.unidades | formatNumber }}
                        </template>
                        <template v-slot:cell(dato)="row">
                            {{ row.item.entrada.folio }}
                        </template>
                    </b-table>
                </b-collapse>
                <hr>
            </div>
            <div v-if="datos.length > 0">
                <b-row>
                    <b-col sm="6"><h6><b>REMISIONES</b></h6></b-col>
                    <b-col class="text-right">
                        <b-button variant="dark" size="sm" v-b-toggle.collapse-3>
                            <span class="when-opened"><i class="fa fa-eye-slash"></i> Ocultar</span>
                            <span class="when-closed"><i class="fa fa-eye"></i> Mostrar</span>
                        </b-button>
                    </b-col>
                </b-row><br>
                <b-collapse visible id="collapse-3">
                    <b-table striped hover :items="datos" :fields="fregistros">
                        <template v-slot:cell(unidades)="row">
                            {{ row.item.unidades | formatNumber }}
                        </template>
                        <template v-slot:cell(dato)="row">
                            {{ row.item.remisione_id }}
                        </template>
                    </b-table>
                </b-collapse>
                <hr>
            </div>
            <div v-if="all_detalles.registers.length > 0">
                <b-row>
                    <b-col sm="6"><h6><b>NOTAS</b></h6></b-col>
                    <b-col class="text-right">
                        <b-button variant="dark" size="sm" v-b-toggle.collapse-5>
                            <span class="when-opened"><i class="fa fa-eye-slash"></i> Ocultar</span>
                            <span class="when-closed"><i class="fa fa-eye"></i> Mostrar</span>
                        </b-button>
                    </b-col>
                </b-row><br>
                <b-collapse visible id="collapse-5">
                    <b-table striped hover :items="all_detalles.registers" :fields="fregistros">
                        <template v-slot:cell(unidades)="row">
                            {{ row.item.unidades | formatNumber }}
                        </template>
                        <template v-slot:cell(dato)="row">
                            {{ row.item.note.folio }}
                        </template>
                    </b-table>
                </b-collapse>
                <hr>
            </div>
            <div v-if="all_detalles.donaciones.length > 0">
                <b-row>
                    <b-col sm="6"><h6><b>DONACIONES</b></h6></b-col>
                    <b-col class="text-right">
                        <b-button variant="dark" size="sm" v-b-toggle.collapse-6>
                            <span class="when-opened"><i class="fa fa-eye-slash"></i> Ocultar</span>
                            <span class="when-closed"><i class="fa fa-eye"></i> Mostrar</span>
                        </b-button>
                    </b-col>
                </b-row><br>
                <b-collapse visible id="collapse-6">
                    <b-table striped hover :items="all_detalles.donaciones" :fields="fregistros">
                        <template v-slot:cell(unidades)="row">
                            {{ row.item.unidades | formatNumber }}
                        </template>
                        <template v-slot:cell(dato)="row">
                            {{ row.item.regalo.plantel }}
                        </template>
                    </b-table>
                </b-collapse>
                <hr>
            </div>
            <div v-if="all_detalles.departures.length > 0">
                <b-row>
                    <b-col sm="6"><h6><b>PROMOCIONES</b></h6></b-col>
                    <b-col class="text-right">
                        <b-button variant="dark" size="sm" v-b-toggle.collapse-7>
                            <span class="when-opened"><i class="fa fa-eye-slash"></i> Ocultar</span>
                            <span class="when-closed"><i class="fa fa-eye"></i> Mostrar</span>
                        </b-button>
                    </b-col>
                </b-row><br>
                <b-collapse visible id="collapse-7">
                    <b-table striped hover :items="all_detalles.departures" :fields="fregistros">
                        <template v-slot:cell(unidades)="row">
                            {{ row.item.unidades | formatNumber }}
                        </template>
                        <template v-slot:cell(dato)="row">
                            {{ row.item.promotion.folio }}
                        </template>
                    </b-table>
                </b-collapse>
            </div>
        </div>
    </div>
</template>

<script>
import LoadComponent from '../cortes/partials/LoadComponent.vue';
import getLibros from './../../mixins/getLibros';
    export default {
        props: ['editoriales'],
        components: { LoadComponent },
        mixins: [getLibros],
        data() {
            return {
                options: [],
                fields: [
                    'editorial', 'ISBN', 'libro', 'existencia', 'entradas',
                    {key: 'devoluciones', label: 'Devolución (Remisiones)'},
                    {key: 'saldevoluciones', label: 'Devolución (Salidas)'},
                    {key: 'prodevoluciones', label: 'Devolución (Promociones)'},
                    {key: 'salidas', label: 'Salidas (Querétaro)'},
                    {key: 'entdevoluciones', label: 'Devolución (Entradas)'},
                    'remisiones', 'notas', 'donaciones', 'promociones', 'defectuosos', 'detalles'
                ],
                fregistros: [ { key: 'dato', label: 'Folio / Plantel' }, 'unidades' ],
                devoluciones: [],
                datos: [],
                entdevoluciones: [],
                tipos: [
                    { value: 'unidades', text: 'UNIDADES' },
                    { value: 'monto', text: 'MONTO' }
                ],
                queryEMov: 'TODO',
                posicion: 0,
                perPage: 20,
                currentPage: 1,
                movimientos: [],
                selected: 'unidades',
                stateDate: null,
                inicio: '0000-00-00',
                final: '0000-00-00',
                selectCategoria: null,
                stateCategoria: null,
                tablaUnidades: true,
                tablaMonto: false,
                tablaCategoria: false,
                all_detalles: {},
                view_detalles: false,
                load: false,
                queryTitulo: '',
                salidas: [],
                prodevoluciones: []
            }
        },
        filters: {
            formatNumber: function (value) {
                return numeral(value).format("0,0[.]00"); 
            }
        },
        mounted: function(){
            this.assign_editorial();
            this.movimientosLibros();
        },
        methods: {
            rowClass(item, type) {
                var entradas = parseInt(item.entradas) + parseInt(item.devoluciones) + parseInt(item.saldevoluciones) + parseInt(item.prodevoluciones);
                var salidas = parseInt(item.remisiones) + parseInt(item.notas) + parseInt(item.promociones) + parseInt(item.donaciones) + parseInt(item.entdevoluciones) + parseInt(item.salidas) + parseInt(item.defectuosos);
                
                var resultado = parseInt(entradas) - parseInt(salidas);
                var piezas = parseInt(item.existencia);
                
                var diferencia = parseInt(piezas) - parseInt(resultado);
                if(!item) return
                if(diferencia !== 0) return 'table-danger';
            },
            movimientosLibros(){
                this.load = true;
                axios.get('/movimientos_todos').then(response => {
                    this.movimientos = response.data;
                    this.queryEMov = 'TODO';
                    this.inicio = '0000-00-00';
                    this.final = '0000-00-00';
                    this.load = false;
                }).catch(error => {
                    this.load = false;
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                });
            },
            // MOSTRAR MOVIMIENTOS POR EDITORIAL
            editorialMov(){
                this.load = true;
                axios.get('/movimientos_por_edit', {params: {queryEMov: this.queryEMov}}).then(response => {
                    this.movimientos = response.data;
                    this.inicio = '0000-00-00';
                    this.final = '0000-00-00';
                    this.load = false;
                }).catch(error => {
                    this.load = false;
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                });
            },
            // OBTENER DETALLES DEL LIBRO
            onDetalles(libro){
                this.load = true;
                this.entdevoluciones = [];
                axios.get('/detalles_movimientos', {params: {titulo: libro.libro}}).then(response => {
                    this.all_detalles = response.data.libro;
                    this.datos = response.data.datos;
                    this.devoluciones = response.data.devoluciones;
                    this.salidas = response.data.salidas;
                    this.prodevoluciones = response.data.prodevoluciones;
                    this.all_detalles.registros.forEach(registro => {
                        registro.entdevoluciones.forEach(entdevolucion => {
                            this.entdevoluciones.push(entdevolucion);
                        });
                    });
                    this.view_detalles = true;
                    this.load = false;
                }).catch(error => {
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                    this.load = false;
                });
            },
            assign_editorial(){
                this.options.push({
                    value: 'TODO',
                    text: 'MOSTRAR TODO'
                });
                this.editoriales.forEach(editorial => {
                    this.options.push({
                        value: editorial.editorial,
                        text: editorial.editorial
                    });
                });
            },
            porCategoriaFecha(){
                if(this.selectCategoria !== null){
                    this.stateCategoria = null;
                    if(this.final != '0000-00-00'){
                        if(this.inicio != '0000-00-00'){
                            axios.get('/movimientos_por_fecha', {params: {inicio: this.inicio, final: this.final, categoria: this.selectCategoria}}).then(response => {
                                this.movimientos = [];
                                this.movimientos = response.data;
                                this.queryEMov = 'TODO';
                                this.selected = 'unidades';

                                this.show_tables(false, false, true);
                            }).catch(error => {
                                this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                            });
                        } else {
                            this.stateDate = false;
                            this.makeToast('warning', 'Es necesario seleccionar la fecha de inicio');
                        }
                    }
                } else{
                    this.stateCategoria = false;
                    this.makeToast('warning', 'Es necesario seleccionar primero la categoria.');
                }
            },
            show_tables(unidades, monto, categoria){
                this.tablaUnidades = unidades;
                this.tablaMonto = monto;
                this.tablaCategoria = categoria;
            },
            makeToast(variant = null, descripcion) {
                this.$bvToast.toast(descripcion, {
                    title: 'Mensaje',
                    variant: variant,
                    solid: true
                })
            },
            mostrarLibros(){
                this.getLibros(this.queryTitulo);
            },
            obtenerLibro(libro){
                this.load = true;
                axios.get('/libro/movimientos_libro', {params: {libro_id: libro.id}}).then(response => {
                    this.movimientos = response.data;
                    this.resultslibros = [];
                    this.load = false;
                }).catch(error => {
                    this.load = false;
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                });
            }
        }
    }
</script>

<style>
.collapsed > .when-opened, :not(.collapsed) > .when-closed {
    display: none;
}
#listaL{
    position: absolute;
    z-index: 100
}
</style>