<template>
    <div>
        <check-connection-component></check-connection-component>
        <div v-if="listadoNotas">
            <b-row>
                <!-- BUSCAR POR NOTA FOLIO -->
                <b-col sm="3">
                    <b-row class="my-1">
                        <b-col sm="4">
                            <label for="input-folio">Folio</label>
                        </b-col>
                        <b-col sm="8">
                            <b-form-input 
                                style="text-transform:uppercase;"
                                id="input-folio" 
                                v-model="folio" 
                                @keyup.enter="porFolio()">
                            </b-form-input>
                        </b-col>
                    </b-row>
                </b-col>
                <!-- BUSCAR NOTA POR CLIENTE -->
                <b-col sm="5">
                    <b-row class="my-1">
                        <b-col sm="2">
                            <label for="input-cliente">Cliente</label>
                        </b-col>
                        <b-col sm="10">
                            <b-input style="text-transform:uppercase;" 
                                v-model="queryCliente" @keyup="porCliente()"></b-input>
                        </b-col>
                    </b-row>
                </b-col> 
                <b-col sm="4">
                    <b-row>
                        <b-col sm="3">
                            <label for="input-inicio">De:</label>
                        </b-col>
                        <b-col sm="9">
                            <input 
                                class="form-control" 
                                type="date" 
                                :state="stateDate"
                                v-model="inicio"
                                @change="porFecha()">
                        </b-col>
                    </b-row>
                    <b-row>
                        <b-col sm="3">
                            <label for="input-final">A: </label>
                        </b-col>
                        <b-col sm="9">
                            <input 
                                class="form-control" 
                                type="date" 
                                v-model="final"
                                @change="porFecha()">
                        </b-col>
                    </b-row>
                </b-col>
            </b-row> 
            <hr>
            <b-row>
                <b-col>
                    <!-- PAGINACIÓN -->
                    <pagination size="default" :limit="1" :data="notesData" 
                        @pagination-change-page="getResults">
                        <span slot="prev-nav"><i class="fa fa-angle-left"></i></span>
                        <span slot="next-nav"><i class="fa fa-angle-right"></i></span>
                    </pagination>
                </b-col>
                <b-col class="text-right">
                    <a 
                        v-if="notes.length > 0"
                        class="btn btn-dark"
                        :href="'/download_note/' + queryCliente + '/' + inicio + '/' + final + '/general'">
                        <i class="fa fa-download"></i> General
                    </a>
                    <a 
                        v-if="notes.length > 0 && (role_id === 1 || role_id == 6)"
                        class="btn btn-dark"
                        :href="'/download_note/' + queryCliente + '/' + inicio + '/' + final + '/detallado'">
                        <i class="fa fa-download"></i> Detallado
                    </a>
                </b-col>
                <b-col sm="3" class="text-right">
                    <div v-if="role_id === 1 || role_id == 2 || role_id == 6">
                        <b-button variant="success" @click="func_crearNota()"><i class="fa fa-plus"></i> Crear nota</b-button>
                    </div>
                </b-col>
            </b-row>
            <!-- LISTADO DE NOTAS -->
            <div v-if="!load">
                <b-table v-if="notes.length > 0" responsive
                    :items="notes" :fields="fieldsN" id="my-table">
                    <template v-slot:cell(created_at)="row">
                        {{ row.item.created_at | moment }}
                    </template>
                    <template v-slot:cell(total_salida)="row">
                        ${{ row.item.total_salida | formatNumber }}
                    </template>
                    <template v-slot:cell(pagos)="row">
                        ${{ row.item.pagos | formatNumber }}
                    </template>
                    <template v-slot:cell(total_devolucion)="row">
                        ${{ row.item.total_devolucion | formatNumber }}
                    </template>
                    <template v-slot:cell(total_pagar)="row">
                        ${{ row.item.total_pagar | formatNumber }}
                    </template>
                    <template v-slot:cell(detalles)="row">
                        <b-button variant="info" @click="detallesNota(row.item)">Detalles</b-button>
                    </template>
                    <template v-slot:cell(pagar)="row">
                        <b-button 
                            v-if="(role_id == 2 || role_id == 6) && row.item.total_pagar > 0" 
                            variant="secondary" 
                            @click="registrarPago(row.item, row.index)">Pago
                        </b-button>
                    </template>
                    <template v-slot:cell(devolucion)="row">
                        <b-button
                            v-if="(role_id == 2 || role_id == 6) && row.item.total_pagar > 0" 
                            variant="primary"
                            @click="registrarDevolucion(row.item, row.index)">Devolución</b-button>
                    </template>
                    <template v-slot:cell(editar)="row">
                        <b-button
                            id="btnNotaE"
                            v-if="(role_id == 6) && row.item.total_pagar > 0" 
                            variant="warning"
                            @click="editarNota(row.item, row.index)"
                            ><i class="fa fa-pencil"></i></b-button>
                    </template>
                    <template #thead-top="row">
                        <tr>
                            <th colspan="3"></th>
                            <th>${{ total_salida | formatNumber }}</th>
                            <!-- <th>${{ total_pagos | formatNumber }}</th>
                            <th>${{ total_devolucion | formatNumber }}</th>
                            <th>${{ total_pagar | formatNumber }}</th> -->
                            <th colspan="2"></th>
                        </tr>
                    </template>
                </b-table>
                <div v-else>
                    <br>
                    <b-alert show variant="dark"><i class="fa fa-warning"></i> No se encontraron registros</b-alert>
                </div>
            </div>
            <div v-else class="text-center text-info my-2 mt-3">
                <b-spinner class="align-middle"></b-spinner>
                <strong>Cargando...</strong>
            </div>
        </div>
        <!-- MOSTRAR DETALLES DE LA NOTA -->
        <div v-if="mostrarDetalles">
            <b-row>
                <b-col>
                    <h5><b>Folio {{ nota.folio }}</b></h5>
                    <label><b>Fecha de creación:</b> {{ nota.created_at | moment }}</label><br>
                    <label><b>Cliente:</b> {{ nota.cliente }}</label>
                </b-col>
                <b-col>
                    <label v-if="nota.entregado_por !== null"><b>Responsable de la entrega:</b> {{ nota.entregado_por }}</label>
                    <label v-if="nota.creado_por !== null"><b>Creado por:</b> {{ nota.creado_por }}</label>
                </b-col>
                <b-col sm="2">
                    <b-button variant="dark" :href="`/download_nota/${nota.id}`"><i class="fa fa-download"></i> Descargar</b-button>
                </b-col>
                <b-col sm="2" align="right">
                    <b-button variant="secondary" @click="mostrarDetalles = false; listadoNotas = true;"><i class="fa fa-mail-reply"></i> Regresar</b-button>
                </b-col>
            </b-row>
            <b-table :items="nota.registers" :fields="fieldsD">
                <template v-slot:cell(index)="row">{{ row.index + 1 }}</template>
                <template v-slot:cell(ISBN)="row">{{ row.item.libro.ISBN }}</template>
                <template v-slot:cell(libro)="row">{{ row.item.libro.titulo }}</template>
                <template v-slot:cell(costo_unitario)="row">${{ row.item.costo_unitario | formatNumber }}</template>
                <template v-slot:cell(total)="row">${{ row.item.total | formatNumber }}</template>
                <template v-slot:cell(pagos)="row">
                    <b-button v-if="row.item.payments.length > 0" variant="outline-info" @click="row.toggleDetails">
                        {{ row.detailsShowing ? 'Ocultar' : 'Mostrar'}}
                    </b-button>
                </template>
                <template v-slot:cell(row-details)="row">
                    <b-card>
                        <b-table :items="row.item.payments" :fields="fieldsP">
                            <template v-slot:cell(index)="row">{{ row.index + 1 }}</template>
                            <template v-slot:cell(unidades)="row">{{ row.item.unidades | formatNumber }}</template>
                            <template v-slot:cell(pago)="row">$ {{ row.item.pago | formatNumber }}</template>
                            <template v-slot:cell(created_at)="row">{{ row.created_at | moment }}</template>
                        </b-table>
                    </b-card>
                </template>
                <template #thead-top="row">
                    <tr>
                        <th colspan="4"></th>
                        <th>{{ nota.total_unidades | formatNumber }}</th>
                        <th>${{ nota.total_salida | formatNumber }}</th>
                        <th colspan="4"></th>
                    </tr>
                </template>
            </b-table>
        </div>
        <!-- REGISTRAR NUEVO PAGO -->
        <div v-if="mostrarNewPago">
            <h4 style="color: #170057">Registrar pago</h4>
            <hr>
            <b-row>
                <b-col>
                    <h5><b>Folio {{ nota.folio }}</b></h5>
                    <label><b>Cliente:</b> {{ nota.cliente }}</label>
                </b-col>
                <b-col>
                    <div class="text-right">
                        <b-button :disabled="load" variant="success" @click="confirmarPagNota()">
                            <i class="fa fa-check"></i> {{ !load ? 'Guardar' : 'Guardando' }} <b-spinner small v-if="load"></b-spinner>
                        </b-button>
                    </div>
                </b-col>
                <b-col align="right">
                    <b-button variant="secondary" @click="mostrarNewPago = false; listadoNotas = true;"><i class="fa fa-mail-reply"></i> Regresar</b-button>
                </b-col>
            </b-row>
            <b-table :items="nota.registers" :fields="fieldsNP">
                <template v-slot:cell(index)="row">{{ row.index + 1 }}</template>
                <template v-slot:cell(ISBN)="row">{{ row.item.libro.ISBN }}</template>
                <template v-slot:cell(libro)="row">{{ row.item.libro.titulo }}</template>
                <template v-slot:cell(costo_unitario)="row">${{ row.item.costo_unitario | formatNumber }}</template>
                <template v-slot:cell(unidades)="row">
                    <b-input 
                        :id="`inpPago-${row.index}`"
                        type="number" 
                        :disabled="load"
                        @change="verificarUnidades(row.item.unidades_base, row.item.unidades_pendiente, row.item.costo_unitario, row.index)" 
                        v-model="row.item.unidades_base">
                    </b-input>
                </template>
                <template v-slot:cell(total)="row">${{ row.item.total_base | formatNumber }}</template>
                <template #thead-top="row">
                    <tr>
                        <th colspan="4"></th>
                        <th>{{ total_unidades | formatNumber }}</th>
                        <th></th>
                        <th>${{ total_vendido | formatNumber }}</th>
                    </tr>
                </template>
            </b-table>
            <!-- MODAL -->
            <b-modal ref="modal-confirmar-pag-nota" size="xl" title="Resumen de la nota">
                <h5><b>Folio {{ nota.folio }}</b></h5>
                <label><b>Cliente:</b> {{ nota.cliente }}</label><br>
                <b-table :items="nota.registers" :fields="fields">
                    <template v-slot:cell(index)="row">{{ row.index + 1 }}</template>
                    <template v-slot:cell(ISBN)="row">{{ row.item.libro.ISBN }}</template>
                    <template v-slot:cell(titulo)="row">{{ row.item.libro.titulo }}</template>
                    <template v-slot:cell(costo_unitario)="row">${{ row.item.costo_unitario | formatNumber }}</template>
                    <template v-slot:cell(unidades)="row">{{ row.item.unidades_base }}</template>
                    <template v-slot:cell(total)="row">${{ row.item.total_base | formatNumber }}</template>
                    <template #thead-top="row">
                        <tr>
                            <th colspan="4"></th>
                            <th>{{ total_unidades | formatNumber }}</th>
                            <th>${{ total_vendido | formatNumber }}</th>
                        </tr>
                    </template>
                </b-table>
                <div slot="modal-footer">
                    <b-row>
                        <b-col sm="10">
                            <b-alert show variant="info">
                                <i class="fa fa-exclamation-circle"></i>
                                <b>Verificar los datos de la nota.</b> En caso de algún error, modificar antes de presionar <b>Confirmar</b> ya que después no se podrán realizar cambios.
                            </b-alert>
                        </b-col>
                        <b-col sm="2" align="right">
                            <b-button :disabled="load" @click="guardarPagosNota()" variant="success">
                                <i class="fa fa-check"></i> Confirmar
                            </b-button>
                        </b-col>
                    </b-row>
                </div>
            </b-modal>
        </div>
        <!-- REGISTRAR DEVOLUCIÓN -->
        <div v-if="mostrarDevolucion">
            <h4 style="color: #170057">Registrar devolución</h4>
            <hr>
            <b-row>
                <b-col>
                    <h5><b>Folio {{ nota.folio }}</b></h5>
                    <label><b>Cliente:</b> {{ nota.cliente }}</label>
                </b-col>
                <b-col>
                    <div class="text-right">
                        <b-button :disabled="load" variant="success" @click="confirmarDevNota()">
                            <i class="fa fa-check"></i> {{ !load ? 'Guardar' : 'Guardando' }} <b-spinner small v-if="load"></b-spinner>
                        </b-button>
                    </div>
                </b-col>
                <b-col align="right">
                    <b-button variant="secondary" @click="mostrarDevolucion = false; listadoNotas = true;"><i class="fa fa-mail-reply"></i> Regresar</b-button>
                </b-col>
            </b-row>
            <b-table :items="nota.registers" :fields="fieldsNP">
                <template v-slot:cell(index)="row">{{ row.index + 1 }}</template>
                <template v-slot:cell(ISBN)="row">{{ row.item.libro.ISBN }}</template>
                <template v-slot:cell(libro)="row">{{ row.item.libro.titulo }}</template>
                <template v-slot:cell(costo_unitario)="row">${{ row.item.costo_unitario | formatNumber }}</template>
                <template v-slot:cell(unidades)="row">
                    <b-input 
                        :id="`inpPago-${row.index}`"
                        type="number" 
                        @change="verificarUnidades(row.item.unidades_base, row.item.unidades_pendiente, row.item.costo_unitario, row.index)" 
                        v-model="row.item.unidades_base">
                    </b-input>
                </template>
                <template v-slot:cell(total)="row">${{ row.item.total_base | formatNumber }}</template>
                <template #thead-top="row">
                    <tr>
                        <th colspan="4"></th>
                        <th>{{ total_unidades | formatNumber }}</th>
                        <th></th>
                        <th>${{ total_vendido | formatNumber }}</th>
                    </tr>
                </template>
            </b-table>

            <b-modal ref="modal-confirmar-dev-nota" size="xl" title="Resumen de la nota">
                <h5><b>Folio {{ nota.folio }}</b></h5>
                <label><b>Cliente:</b> {{ nota.cliente }}</label><br>
                <b-table :items="nota.registers" :fields="fields">
                    <template v-slot:cell(index)="row">{{ row.index + 1 }}</template>
                    <template v-slot:cell(ISBN)="row">{{ row.item.libro.ISBN }}</template>
                    <template v-slot:cell(titulo)="row">{{ row.item.libro.titulo }}</template>
                    <template v-slot:cell(costo_unitario)="row">${{ row.item.costo_unitario | formatNumber }}</template>
                    <template v-slot:cell(total)="row">${{ row.item.total_base | formatNumber }}</template>
                    <template v-slot:cell(unidades)="row">{{ row.item.unidades_base }}</template>
                    <template #thead-top="row">
                        <tr>
                            <th colspan="4"></th>
                            <th>{{ total_unidades | formatNumber }}</th>
                            <th>${{ total_vendido | formatNumber }}</th>
                        </tr>
                    </template>
                </b-table>
                <div slot="modal-footer">
                    <b-row>
                        <b-col sm="10">
                            <b-alert show variant="info">
                                <i class="fa fa-exclamation-circle"></i>
                                <b>Verificar los datos de la nota.</b> En caso de algún error, modificar antes de presionar <b>Confirmar</b> ya que después no se podrán realizar cambios.
                            </b-alert>
                        </b-col>
                        <b-col sm="2" align="right">
                            <b-button :disabled="load" @click="guardarDevolucion()" variant="success">
                                <i class="fa fa-check"></i> Confirmar
                            </b-button>
                        </b-col>
                    </b-row>
                </div>
            </b-modal>
        </div>
        <!-- CREAR / EDITAR UNA NOTA -->
        <div v-if="mostrarCrearNota">
            <b-row>
                <b-col sm="3">
                    <h4 style="color: #170057">{{ editar ? 'Editar' : 'Crear' }} nota</h4>
                </b-col>
                <b-col sm="6" align="right">
                    <b-button 
                        variant="success" 
                        :disabled="load"
                        @click="confirmarNota()">
                        <i class="fa fa-check"></i> {{ !load ? 'Guardar' : 'Guardando' }} <b-spinner small v-if="load"></b-spinner>
                    </b-button>
                </b-col>
                <b-col sm="3" align="right">
                    <b-button variant="secondary" @click="mostrarCrearNota = false; listadoNotas = true;"><i class="fa fa-mail-reply"></i> Regresar</b-button>
                </b-col>
            </b-row>
            <hr>
            <b-row class="col-md-6">
                <b-col sm="3">Cliente <b id="txtObligatorio">*</b></b-col>
                <b-col sm="9">
                    <b-form-input 
                        style="text-transform:uppercase;"
                        v-model="cliente" 
                        autofocus 
                        :disabled="load" 
                        :state="state" 
                        @change="check_cliente()">
                    </b-form-input>
                </b-col>
            </b-row>
            <b-row class="col-md-8">
                <b-col sm="4">Responsable de la entrega <b id="txtObligatorio">*</b></b-col>
                <b-col sm="8"><b-form-select :state="stateResp" v-model="entregado_por" :options="options"></b-form-select></b-col>
            </b-row><br>
            <div>
                <b-table :items="registers" :fields="fields">
                    <template v-slot:cell(index)="row">{{ row.index + 1 }}</template>
                    <template v-slot:cell(ISBN)="row">{{ row.item.libro.ISBN }}</template>
                    <template v-slot:cell(titulo)="row">{{ row.item.libro.titulo }}</template>
                    <template v-slot:cell(costo_unitario)="row">${{ row.item.costo_unitario | formatNumber }}</template>
                    <template v-slot:cell(total)="row">${{ row.item.total | formatNumber }}</template>
                    <template v-slot:cell(eliminar)="row">
                        <b-button 
                            variant="danger"
                            @click="eliminarRegistro(row.item, row.index)" 
                            :disabled="load"
                            v-if="editar == false">
                            <i class="fa fa-minus-circle"></i>
                        </b-button>
                    </template>
                    <template #thead-top="row">
                        <tr>
                            <th colspan="1"></th>
                            <th>ISBN</th>
                            <th>Libro</th>
                            <th>Costo unitario</th>
                            <th>Unidades</th>
                        </tr>
                        <tr>
                            <th colspan="1"></th>
                            <th>
                                <b-input
                                    id="input-isbn"
                                    autofocus
                                    v-model="isbn"
                                    @keyup.enter="buscarLibroISBN()"
                                    v-if="inputISBN"
                                    :disabled="load"
                                ></b-input>
                                <label v-if="!inputISBN">{{ temporal.libro.ISBN }}</label>
                            </th>
                            <th>
                                <b-input
                                    id="input-libro"
                                    autofocus
                                    v-model="queryTitulo"
                                    @keyup="mostrarLibros()"
                                    v-if="inputLibro"
                                    :disabled="load">
                                </b-input>
                                <div class="list-group" v-if="resultslibros.length" id="listaBL">
                                    <a 
                                        href="#" 
                                        v-bind:key="i" 
                                        class="list-group-item list-group-item-action" 
                                        v-for="(libro, i) in resultslibros" 
                                        @click="datosLibro(libro)">
                                        {{ libro.titulo }}
                                    </a>
                                </div>
                                <label v-if="!inputLibro">{{ temporal.libro.titulo }}</label>
                            </th>
                            <th>
                                <b-form-input 
                                    id="input-costo"
                                    type="number" 
                                    autofocus
                                    v-model="costo_unitario"
                                    v-if="inputCosto"
                                    @keyup.enter="guardarCosto()"
                                    :disabled="load">
                                </b-form-input> 
                            </th>
                            <th>
                                <b-form-input 
                                    autofocus
                                    id="input-unidades"
                                    @keyup.enter="guardarRegistro()"
                                    v-if="inputUnidades"
                                    v-model="unidades" 
                                    type="number"
                                    required
                                    :disabled="load">
                                </b-form-input>
                            </th>
                            <th>
                                <b-button 
                                    variant="secondary"
                                    @click="eliminarTemporal()" 
                                    v-if="inputCosto"
                                    :disabled="load">
                                    <i class="fa fa-minus-circle"></i>
                                </b-button>
                            </th>
                        </tr>
                        <tr>
                            <th colspan="4"></th>
                            <th>{{ total_unidades }}</th>
                            <th>${{ total }}</th>
                            <th></th>
                        </tr>
                    </template>
                </b-table>
                <hr>
            </div>
            <!-- MODAL DE CONFIRMAR -->
            <b-modal ref="modal-confirmar-nota" size="xl" title="Resumen de la nota">
                <label><b>Cliente: </b><label style="text-transform:uppercase;">{{ cliente }}</label></label>
                <br>
                <b-table :items="registers" :fields="fields">
                    <template v-slot:cell(index)="row">{{ row.index + 1 }}</template>
                    <template v-slot:cell(ISBN)="row">{{ row.item.libro.ISBN }}</template>
                    <template v-slot:cell(titulo)="row">{{ row.item.libro.titulo }}</template>
                    <template v-slot:cell(costo_unitario)="row">${{ row.item.costo_unitario | formatNumber }}</template>
                    <template v-slot:cell(total)="row">${{ row.item.total | formatNumber }}</template>
                    <template #thead-top="row">
                        <tr>
                            <th colspan="4"></th>
                            <th>{{ total_unidades | formatNumber }}</th>
                            <th>${{ total | formatNumber }}</th>
                            <th></th>
                        </tr>
                    </template>
                </b-table>
                <div slot="modal-footer">
                    <b-row>
                        <b-col sm="10">
                            <b-alert show variant="info">
                                <i class="fa fa-exclamation-circle"></i>
                                <b>Verificar los datos de la nota.</b> En caso de algún error, modificar antes de presionar <b>Confirmar</b> ya que después no se podrán realizar cambios.
                            </b-alert>
                        </b-col>
                        <b-col sm="2" align="right">
                            <b-button 
                                v-if="editar == false" 
                                variant="success" 
                                :disabled="load"
                                @click="guardarNota()">
                                <i class="fa fa-check"></i> Confirmar
                            </b-button>
                            <b-button 
                                v-if="editar == true" 
                                variant="success" 
                                :disabled="load"
                                @click="actualizarNota()">
                                <i class="fa fa-check"></i> Confirmar
                            </b-button>
                        </b-col>
                    </b-row>
                </div>
            </b-modal>
        </div> 
    </div>
</template>

<script>
import setResponsables from '../../mixins/setResponsables';
import getLibros from '../../mixins/getLibros';
    export default {
        props: ['role_id', 'listresponsables'],
        mixins: [setResponsables,getLibros],
        data() {
            return {
                cliente: '',
                viewForm: false,
                registers: [],
                fields: [
                    {key: 'index', label: 'N.'},
                    'ISBN',
                    {key: 'titulo', label: 'Libro'},
                    {key: 'costo_unitario', label: 'Costo unitario'},
                    'unidades',
                    {key: 'total', label: 'Subtotal'},
                    {key: 'eliminar', label: ''}
                ],
                fieldsP: [
                    {key: 'index', label: 'N.'}, 
                    'unidades',
                    'pago', 
                    {key: 'created_at', label: 'Fecha'}, 
                ],
                fieldsN: [
                    'folio',
                    'cliente',
                    {key: 'created_at', label: 'Fecha de creación'},
                    {key: 'total_salida', label: 'Salida'},
                    // 'pagos',
                    // {key: 'total_devolucion', label: 'Devolución'},
                    // {key: 'total_pagar', label: 'Pagar'},
                    {key: 'detalles', label: ''},
                    // {key: 'pagar', label: ''},
                    // {key: 'devolucion', label: ''},
                    {key: 'editar', label: ''},
                ],
                fieldsD: [
                    {key: 'index', label: 'N.'},
                    'ISBN', 'libro',
                    {key: 'costo_unitario', label: 'Costo unitario'},
                    'unidades',
                    {key: 'total', label: 'Subtotal'}
                    // {key: 'unidades_pagado', label: 'Unidades vendidas', variant: 'info'},
                    // {key: 'unidades_devuelto', label: 'Unidades devueltas', variant: 'info'},
                    // {key: 'unidades_pendiente', label: 'Unidades pendientes', variant: 'info'},
                    // 'pagos'
                ],
                fieldsNP: [
                    {key: 'index', label: 'N.'},
                    'ISBN', 'libro',
                    {key: 'costo_unitario', label: 'Costo unitario'},
                    'unidades',
                    {key: 'unidades_pendiente', label: 'Unidades pendientes'},
                    {key: 'total', label: 'Subtotal'},
                ],
                state: null,
                isbn: '',
                inputISBN: true,
                temporal: {},
                queryTitulo: '',
                inputLibro: true,
                inputUnidades: false,
                unidades: '',
                costo_unitario: '',
                inputCosto: false,
                load: false,
                nota: {},
                notes: [],
                mostrarDetalles: false,
                mostrarCrearNota: false,
                mostrarNewPago: false,
                total_vendido: 0,
                btnGuardar: false,
                posicion: 0,
                mostrarDevolucion: false,
                listadoNotas: true,
                editar: false,
                eliminados: [],
                nuevos: [],
                folio: null,
                queryCliente: null,
                perPage: 10,
                currentPage: 1,
                loadRegisters: false,
                inicio: '0000-00-00',
                final: '0000-00-00',
                stateDate: null,
                total_salida: 0,
                total_devolucion: 0,
                total_pagar: 0,
                total_pagos: 0,
                total_unidades: 0,
                total: 0,
                entregado_por: null,
                options: [],
                stateResp: null,
                notesData: {},
                searchCliente: false,
                searchFecha: false
            }
        },
        filters: {
            moment: function (date) {
                return moment(date).format('DD-MM-YYYY');
            },
            formatNumber: function (value) {
                return numeral(value).format("0,0[.]00"); 
            }
        },
        mounted: function(){
            this.getResults();
            this.acumular_totales();
            this.get_responsables();
        },
        methods: {
            getResults(page = 1){
                if(!this.searchCliente && !this.searchFecha)
                    this.http_notas(page);
                if(this.searchCliente)
                    this.http_cliente(page);
                if(this.searchFecha)
                    this.http_fecha(page);
            },
            set_search(searchCliente, searchFecha){
                this.searchCliente = searchCliente;
                this.searchFecha = searchFecha;
            },
            // HTTP REMCLIENTE
            http_notas(page = 1){
                this.load = true;
                axios.get(`/notes/index?page=${page}`).then(response => {
                    this.notesData = response.data; 
                    this.notes = response.data.data;
                    this.set_search(false, false);
                    this.load = false;   
                }).catch(error => {
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                    this.load = false;
                });
            },
            get_responsables(){
                this.load = true;
                this.options = [];
                axios.get('/remisiones/get_responsables').then(response => {
                    this.options = this.assign_responsables(this.options, response.data);
                    this.load = false;
                }).catch(error => {
                    this.load = false;
                });
            },
            confirmarNota(){
                this.stateResp = true;
                if(this.cliente.length > 4 && this.registers.length > 0 && this.entregado_por !== null){
                    this.state = true;
                    this.$refs['modal-confirmar-nota'].show();
                }
                else{
                    if(this.entregado_por === null) this.stateResp = false;
                    if(this.cliente.length <= 4) this.state = false;
                    this.makeToast('warning', 'Verificar que todos los datos esten escritos correctamente.');
                }
            },
            confirmarDevNota(){
                if(this.total_vendido > 0){
                    this.$refs['modal-confirmar-dev-nota'].show();
                } else {
                    this.makeToast('warning', 'El total no puede ser 0.');
                }
            },
            confirmarPagNota(){
                if(this.total_vendido > 0){
                    this.$refs['modal-confirmar-pag-nota'].show();
                } else {
                    this.makeToast('warning', 'El total no puede ser 0.');
                }
            },
            acumular_totales(){
                this.total_salida = 0;
                this.total_devolucion = 0;
                this.total_pagar = 0;
                this.total_pagos = 0;
                this.notes.forEach(note => {
                    this.total_salida += note.total_salida;
                    this.total_devolucion += note.total_devolucion;
                    this.total_pagar += note.total_pagar;
                    this.total_pagos += note.pagos;
                });
            },
            acum_total_note(){
                this.total_unidades = 0;
                this.total = 0;
                this.registers.forEach(register => {
                    this.total_unidades += parseInt(register.unidades);
                    this.total += parseInt(register.total);
                });
            },
            // BUSCAR NOTAS POR FECHA
            porFecha(){
                if(this.final != '0000-00-00'){
                    if(this.inicio != '0000-00-00'){
                        this.http_fecha();
                    } else {
                        this.stateDate = false;
                        this.makeToast('warning', 'Es necesario seleccionar la fecha de inicio');
                    }
                }
            },
            http_fecha(page = 1){
                this.load = true;
                axios.get(`/notes/by_fecha?page=${page}`, {
                    params: {inicio: this.inicio, final: this.final, cliente: this.queryCliente}}).then(response => {
                    this.notesData = response.data;
                    this.notes = response.data.data;
                    this.acumular_totales();
                    this.set_search(false, true);
                    this.load = false;
                }).catch(error => {
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                    this.load = false;
                });
            },
            // BUSCAR NOTA POR FOLIO
            porFolio(){
                this.load = true;
                axios.get('/notes/by_folio', {params: {folio: this.folio}}).then(response => {
                    if(response.data.id != undefined){
                        this.notesData = {};
                        this.notes = [];
                        this.notes.push(response.data);
                        this.set_search(false, false);
                        this.acumular_totales();
                    }
                    else{
                        this.makeToast('warning', 'El folio no existe');
                    }
                    this.load = false;
                }).catch(error => {
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                    this.load = false;
                });
            },
            // BUSCAR NOTA POR CLIENTE
            porCliente(){
                if(this.queryCliente !== null){
                    if(this.queryCliente.length > 0){
                        this.http_cliente();
                    } else {
                        this.queryCliente = null;
                    }
                }
            },
            http_cliente(page = 1){
                this.load = true;
                axios.get(`/notes/by_cliente?page=${page}`, {params: {queryCliente: this.queryCliente}}).then(response => {
                    this.notesData = response.data;
                    this.notes = response.data.data;
                    this.acumular_totales();
                    this.inicio = '0000-00-00';
                    this.final = '0000-00-00';
                    this.set_search(true, false);
                    this.load = false;
                }).catch(error => {
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                    this.load = false;
                });
            },
            // INICIALIZAR PARA CREAR NOTA
            func_crearNota(){
                this.listadoNotas = false;
                this.mostrarCrearNota = true;
                this.nota = {};
                this.cliente = '';
                this.registers = [];
                this.editar = false;
                this.entregado_por = null;
                this.stateResp = null;
                this.state = null;
            },
            // MOSTRAR DETALLES DE LA NOTA
            detallesNota(nota){
                this.nota = {};
                this.nota.total_unidades = 0;
                axios.get('/detalles_nota', {params: {note_id: nota.id}}).then(response => {
                    this.nota.id = nota.id;
                    this.nota.folio = nota.folio;
                    this.nota.cliente = nota.cliente;
                    this.nota.total_salida = nota.total_salida;
                    this.nota.registers = response.data;
                    this.nota.entregado_por = nota.entregado_por;
                    this.nota.creado_por = nota.creado_por;
                    this.nota.registers.forEach(register => {
                        this.nota.total_unidades += register.unidades;
                    });
                    this.listadoNotas = false;
                    this.mostrarDetalles = true;
                });
            },
            // REGISTRAR PAGO DE LA NOTA
            registrarPago(nota, i){
                this.nota = {};
                this.posicion = i;
                axios.get('/detalles_nota', {params: {note_id: nota.id}}).then(response => {
                    this.nota.id = nota.id;
                    this.nota.folio = nota.folio;
                    this.nota.cliente = nota.cliente;
                    this.nota.total_salida = nota.total_salida;
                    this.nota.registers = response.data;
                    this.acum_vendidos();
                    this.listadoNotas = false;
                    this.mostrarNewPago = true;
                });
            },
            // REGISTRAR DEVOLUCIÓN
            registrarDevolucion(nota, i){
                this.nota = {};
                this.posicion = i;
                axios.get('/detalles_nota', {params: {note_id: nota.id}}).then(response => {
                    this.nota.id = nota.id;
                    this.nota.folio = nota.folio;
                    this.nota.cliente = nota.cliente;
                    this.nota.total_salida = nota.total_salida;
                    this.nota.registers = response.data;
                    this.acum_vendidos();
                    this.listadoNotas = false;
                    this.mostrarDevolucion = true;
                });
            },
            // INICIALIZAR PARA EDITAR LA NOTA
            editarNota(nota, i){
                this.editar = true;
                this.cliente = '';
                this.registers = [];
                this.nota = {};
                this.posicion = i;
                this.nuevos = [];
                this.eliminados = [];
                this.resultslibros = [];
                this.queryTitulo = '';
                axios.get('/detalles_nota', {params: {note_id: nota.id}}).then(response => {
                    this.nota.id = nota.id;
                    this.nota.folio = nota.folio;
                    this.cliente = nota.cliente;
                    this.nota.total_salida = nota.total_salida;
                    this.registers = response.data;
                    this.entregado_por = nota.entregado_por;
                    this.listadoNotas = false;
                    this.mostrarCrearNota = true;
                    this.viewForm = true;
                    this.acum_total_note();
                });
            },
            // GUARDAR PAGOS DE NOTA
            guardarPagosNota(){
                this.load = true;
                axios.post('/guardar_pago', this.nota).then(response => {
                    this.notes[this.posicion] = response.data;
                    this.acumular_totales();
                    this.makeToast('success', 'El pago se guardo correctamente');
                    this.$refs['modal-confirmar-pag-nota'].hide();
                    this.mostrarNewPago = false;
                    this.listadoNotas = true;
                    this.load = false;
                })
                .catch(error => {
                    this.makeToast('danger', 'Ocurrio un error, vuelve a intentarlo');
                    this.load = false;
                });
            },
            // VERIFICAR UNIDADES PARA OBTENER EL SUBTOTAL
            verificarUnidades(unidades, pendiente, costo_unitario, i){
                if(unidades < 0){
                    this.makeToast('warning', 'Las unidades tienen que ser mayor a cero.');
                    this.nota.registers[i].unidades_base = 0;
                    this.nota.registers[i].total_base = 0;
                }
                if(unidades > pendiente){
                    this.makeToast('warning', 'Las unidades son mayor a lo pendiente');
                    this.nota.registers[i].unidades_base = 0;
                    this.nota.registers[i].total_base = 0;
                }
                if(unidades >= 0 && unidades <= pendiente){
                    this.total_vendido = 0;
                    this.nota.registers[i].total_base = unidades * costo_unitario;
                    this.btnGuardar = true;
                    if(i + 1 < this.nota.registers.length){
                        document.getElementById('inpPago-'+(i+1)).focus();
                        document.getElementById('inpPago-'+(i+1)).select();
                    }
                }
                this.acum_vendidos();
            },
            acum_vendidos(){
                this.total_unidades = 0;
                this.total_vendido = 0;
                this.nota.registers.forEach(register => {
                    this.total_unidades += parseInt(register.unidades_base);
                    this.total_vendido += parseInt(register.total_base);
                });
            },
            // GUARDAR DEVOLUCIÓN
            guardarDevolucion(){
                this.load = true;
                axios.post('/guardar_devolucion', this.nota).then(response => {
                    this.notes[this.posicion] = response.data;
                    this.acumular_totales();
                    this.makeToast('success', 'La devolución se guardo correctamente');
                    this.$refs['modal-confirmar-dev-nota'].hide();
                    this.mostrarDevolucion = false;
                    this.listadoNotas = true;
                    this.load = false;
                })
                .catch(error => {
                    this.makeToast('danger', 'Ocurrio un error, vuelve a intentarlo');
                    this.load = false;
                });
            },
            // GUARDAR NOTA
            guardarNota(){
                this.load = true;
                if(this.cliente.length > 4){
                    this.state = null;
                    this.nota.cliente = this.cliente;
                    this.nota.registers = this.registers;
                    this.nota.entregado_por = this.entregado_por;
                    axios.post('/guardar_nota', this.nota).then(response => {
                        this.load = false;
                        this.notes.unshift(response.data);
                        this.acumular_totales();
                        this.$refs['modal-confirmar-nota'].hide();
                        this.makeToast('success', 'La nota se creo correctamente');
                        this.mostrarCrearNota = false;
                        this.listadoNotas = true;
                    })
                    .catch(error => {
                        this.load = false;
                        this.makeToast('danger', 'Ocurrio un problema, vuelve a intentar');
                    });
                }
                else{
                    this.state = false;
                    this.load = false;
                    this.makeToast('warning', 'Campo obligatorio, mayor a 5 caracteres');
                }
            },
            // ACTUALIZAR DATOS DE NOTA
            actualizarNota(){
                this.load = true;
                if(this.cliente.length > 4){
                    this.state = null;
                    this.nota.cliente = this.cliente;
                    this.nota.entregado_por = this.entregado_por;
                    this.nota.nuevos = this.nuevos;
                    this.nota.eliminados = this.eliminados;
                    axios.post('/actualizar_nota', this.nota).then(response => {
                        this.load = false;
                        this.makeToast('success', 'La nota se actualizo correctamente');
                        this.notes[this.posicion].total_salida = response.data.total_salida;
                        this.notes[this.posicion].total_pagar = response.data.total_pagar;
                        this.$refs['modal-confirmar-nota'].hide();
                        this.acumular_totales();
                        this.mostrarCrearNota = false;
                        this.listadoNotas = true;
                    })
                    .catch(error => {
                        this.load = false;
                        this.makeToast('danger', 'Ocurrio un problema, vuelve a intentar');
                    });
                }
                else{
                    this.state = false;
                    this.load = false;
                    this.makeToast('warning', 'Campo obligatorio, mayor a 5 caracteres');
                }
            },
            // VALIDAR EL CAMPO CLIENTE
            check_cliente(){
                if(this.cliente.length > 4){
                    this.viewForm = true;
                    this.state = true;
                }
                else{
                    this.state = false;
                    this.makeToast('warning', 'Campo obligatorio, mayor a 5 caracteres');
                }
            },
            // BUSCAR LIBRO POR ISBN
            buscarLibroISBN(){
                axios.get('/buscarISBN', {params: {isbn: this.isbn}}).then(response => {
                    this.datosLibro(response.data);
                }).catch(error => {
                    this.makeToast('danger', 'ISBN incorrecto');
                });
            }, 
            // MOSTRAR COINCIDENCIAS DE LIBROS
            mostrarLibros(){
                this.getLibros(this.queryTitulo);
            },
            // SELECCIONAR LIBRO
            datosLibro(libro){
                this.temporal = {
                    id: libro.id,
                    libro: {
                        ISBN: libro.ISBN,
                        titulo: libro.titulo,
                        piezas: libro.piezas,
                    },
                    costo_unitario: 0,
                    unidades: 0,
                    total: 0
                };
                this.ini_1();
            },
            // VERIFICAR QUE EL COSTO SEA VALIDO
            guardarCosto(){
                if(this.costo_unitario > 0){
                    this.inputUnidades = true;
                    this.temporal.costo_unitario = this.costo_unitario;
                }
                else{
                    this.makeToast('warning', 'Costo invalido');
                }
                
            },
            // GUARDAR REGISTRO TEMPORAL EN ARRAY
            guardarRegistro(){
                if(this.unidades > 0){
                    if(this.unidades <= this.temporal.libro.piezas){
                        this.temporal.unidades = this.unidades;
                        this.temporal.total = this.temporal.unidades * this.temporal.costo_unitario;
                        if(this.editar == true){
                            this.nuevos.push(this.temporal);
                        }
                        this.registers.push(this.temporal);
                        this.eliminarTemporal();
                        this.acum_total_note();
                    }
                    else{
                        this.makeToast('warning', `${this.temporal.libro.piezas} unidades en existencia`);
                    }
                }
                else{
                    this.makeToast('warning', 'Unidades invalidas');
                }
            },
            // ELIMINAR DATOS TEMPORALES
            eliminarTemporal(){
                this.inputUnidades = false;
                this.inputLibro = true;
                this.inputISBN = true;
                this.queryTitulo = '';
                this.temporal = {};
                this.unidades = '';
                this.costo_unitario = '';
                this.inputCosto = false;
                this.isbn = '';
            },
            // ELIMINAR REGISTRO DEL ARRAY
            eliminarRegistro(registro, i){
                if(this.editar == true){
                    this.eliminados.push(registro);
                }
                this.registers.splice(i, 1);
                this.acum_total_note();
            },
            ini_1(){
                this.inputLibro = false;
                this.inputISBN = false;
                this.inputCosto = true;
                this.resultslibros = [];
            },
            makeToast(variant = null, descripcion) {
                this.$bvToast.toast(descripcion, {
                    title: 'Mensaje',
                    variant: variant,
                    solid: true
                })
            }
        }
    }
</script>

<style scoped>
    #btnNotaE{
        color: white;
    }
    #listaBL{
        position: absolute;
        z-index: 100
    }
    #txtObligatorio {
        color: red;
    }
</style>
