<template>
    <div>
        <h4 style="color: #170057">Registro de devolución</h4><hr>
        <b-row>
            <b-col>
                <h5><b>Remisión No. {{ form.remisione_id }}</b></h5>
            </b-col>
            <b-col sm="3" class="text-right">
                <b-button 
                    :disabled="load" variant="success" pill
                    @click="confirmarDevolucion()">
                    <i class="fa fa-check"></i> {{ !load ? 'Guardar' : 'Guardando' }} <b-spinner small v-if="load"></b-spinner>
                </b-button>
            </b-col>
            <!-- <div class="col-md-3 text-right">
                <b-button variant="secondary" @click="mostrarDevolucion = false">
                    <i class="fa fa-mail-reply"></i> Regresar
                </b-button>
            </div> -->
        </b-row>
        <label><b>Cliente:</b> {{ form.cliente }}</label>
        <hr>
        <b-row>
            <b-col>
                <b-form-group label-cols="2" label-cols-lg="4" label="Fecha de la devolución" label-for="input-fecha">
                    <b-form-input v-model="form.fecha_devolucion" 
                        type="date" :disabled="load" id="input-fecha">
                    </b-form-input>
                </b-form-group>
            </b-col>
            <b-col>
                <b-form-group label-cols="2" label-cols-lg="4" label="Entregado por" label-for="input-entregadopor">
                    <b-form-select v-model="form.entregado_por"
                        :state="state" :options="options" id="input-entregadopor">
                    </b-form-select>
                </b-form-group>
            </b-col>
        </b-row>
        <br>
        <table class="table">
            <thead>
                <tr>
                    <td></td><td></td>
                    <td></td><td></td><td></td>
                    <td><h6><b>${{ form.total_devolucion | formatNumber }}</b></h6></td>
                </tr>
                <tr>
                    <th scope="col">ISBN</th>
                    <th scope="col">Libro</th>
                    <th scope="col">Costo unitario</th>
                    <th scope="col">Unidades pendientes</th>
                    <th scope="col">Unidades</th>
                    <th scope="col">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(devolucion, i) in form.devoluciones" v-bind:key="i">
                    <td>{{ devolucion.ISBN }}</td>
                    <td>{{ devolucion.titulo }}</td>
                    <td>$ {{ devolucion.costo_unitario | formatNumber }}</td>
                    <td>{{ devolucion.unidades_resta | formatNumber }}</td>
                    <td>
                        <b-input :id="`inpDev-${i}`" type="number" 
                            v-model="devolucion.unidades_base" :disabled="load"
                            @change="guardarUnidades(devolucion, i)"/>
                    </td>
                    <td>$ {{ devolucion.total_base | formatNumber }}</td>
                </tr>
            </tbody>
        </table>
        <!-- MODAL -->
        <b-modal ref="modal-confirmarDevolucion" size="xl" title="Resumen de la devolución">
            <h5><b>Remisión No. {{ form.remisione_id }}</b></h5>
            <label><b>Cliente:</b> {{ form.cliente }}</label><br>
            <label><b>Devolución entregada por:</b> {{ form.entregado_por }}</label>
            <b-table :items="form.devoluciones" :fields="fieldsRP">
                <template v-slot:cell(isbn)="row">{{ row.item.ISBN }}</template>
                <template v-slot:cell(libro)="row">{{ row.item.titulo }}</template>
                <template v-slot:cell(costo_unitario)="row">${{ row.item.costo_unitario | formatNumber }}</template>
                <template v-slot:cell(subtotal)="row">${{ row.item.total_base | formatNumber }}</template>
                <template #thead-top="row">
                    <tr>
                        <th colspan="5"></th>
                        <th>${{ form.total_devolucion | formatNumber }}</th>
                    </tr>
                </template>
            </b-table>
            <div slot="modal-footer">
                <b-row>
                    <b-col sm="9">
                        <b-alert show variant="info">
                            <i class="fa fa-exclamation-circle"></i> <b>Verificar la devolución.</b> En caso de algún error, modificar antes de presionar <b>Confirmar</b> ya que después no se podrán realizar cambios.
                        </b-alert>
                    </b-col>
                    <b-col sm="3" align="right">
                        <b-button 
                            :disabled="load" variant="success"
                            @click="guardar()">
                            <i class="fa fa-check"></i> Confirmar
                        </b-button>
                    </b-col>
                </b-row>
            </div>
        </b-modal>
    </div>
</template>

<script>
    import sweetAlert from '../../mixins/sweetAlert';
    export default {
    props: ['remision'],
        mixins: [sweetAlert],
        data() {
            return {
                load: false,
                fieldsRP: [
                    {key: 'isbn', label: 'ISBN'}, 
                    'libro', 
                    {key: 'costo_unitario', label: 'Costo unitario'}, 
                    {key: 'unidades_resta', label: 'Unidades pendientes'},
                    {key: 'unidades_base', label: 'Unidades'}, 
                    'subtotal'
                ],
                state: null,
                form: {
                    remisione_id: null,
                    fecha_devolucion: null,
                    cliente: null,
                    entregado_por: null,
                    total_devolucion: 0,
                    total_pagar: 0,
                    devoluciones: []
                }
            }
        },
        filters: {
            formatNumber: function (value) {
                return numeral(value).format("0,0[.]00"); 
            }
        },
        created: function(){
            this.get_responsables();
            this.assign_remision();
        },
        methods: {
            assign_remision(){
                this.form.remisione_id = this.remision.id;
                this.form.cliente = this.remision.cliente.name;
                this.form.total_pagar = this.remision.total_pagar;
                this.remision.devoluciones.forEach(d => {
                    this.form.devoluciones.push({
                        devolucion_id: d.id,
                        libro_id: d.dato.libro_id,
                        ISBN: d.dato.libro.ISBN,
                        titulo: d.dato.libro.titulo,
                        costo_unitario: d.dato.costo_unitario,
                        unidades_resta: d.unidades_resta,
                        unidades_base: 0,
                        total_base: 0
                        // dato_id: rd.dato_id,
                        // total: rd.total,
                        // total_resta: rd.total_resta,
                        // unidades: rd.unidades
                    });
                });
            },
            guardarUnidades(devolucion, i){
                if(devolucion.unidades_base >= 0){
                    if(devolucion.unidades_base <= devolucion.unidades_resta){
                        this.form.devoluciones[i].total_base = devolucion.costo_unitario * devolucion.unidades_base;
                        if(i + 1 < this.form.devoluciones.length){
                            document.getElementById('inpDev-'+(i+1)).focus();
                            document.getElementById('inpDev-'+(i+1)).select();
                        }
                    }
                    else{
                        this.item = devolucion.id;
                        this.makeToast('warning', 'Unidades mayores a unidades pendientes.');
                        this.form.devoluciones[i].unidades_base = 0;
                        this.form.devoluciones[i].total_base = 0;
                    }
                }
                else{
                    this.makeToast('warning', 'Las unidades no pueden ser menores a cero');
                    this.form.devoluciones[i].unidades_base = 0;
                    this.form.devoluciones[i].total_base = 0;
                }
                this.acumularFinal();
            },
            acumularFinal(){
                this.form.total_devolucion = 0;
                // this.total_pagar = 0;
                this.form.devoluciones.forEach(devolucion => {
                    this.form.total_devolucion += devolucion.total_base;
                    // this.total_pagar += devolucion.total_resta;
                });
            },
            // MOSTRAR RESUMEN DE LA DEVOLUCIÓN PARA CONFIRMAR
            confirmarDevolucion(){
                if(this.form.entregado_por != null){
                    this.state = true;
                    if(this.form.total_devolucion > 0){
                        if(this.form.total_devolucion <= this.form.total_pagar){
                            this.$refs['modal-confirmarDevolucion'].show();
                        } else {    
                            this.makeToast('warning', 'La devolución no puede ser guardada. El total de la devolución es mayor al total por pagar.');
                        }
                    } else {
                        this.makeToast('warning', 'El total debe ser mayor a cero.');
                    }
                } else{
                    this.state = false;
                    this.makeToast('warning', 'Seleccionar la opción de devolución entregada por, para poder continuar.');
                }
            },
            // GUARDAR DEVOLUCIÓN
            guardar(){
                this.load = true;
                axios.put('/devoluciones/historial_update', this.form).then(response => {
                    this.$refs['modal-confirmarDevolucion'].hide();
                    this.load = false;
                    this.messageAlert('center', 'success', 'La devolución se guardo correctamente.', null, 'reload');
                }).catch(error => {
                    this.load = false;
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
            get_responsables(){
                this.load = true;
                this.options = [];
                axios.get('/remisiones/get_responsables').then(response => {
                    this.options = this.assign_responsables(response.data);
                    this.load = false;
                }).catch(error => {
                    this.load = false;
                });
            },
            assign_responsables(responsables){
                let options = [];
                options.push(
                    {
                        value: null,
                        text: 'Selecciona una opción',
                        disabled: true
                    },
                    { value: 'CLIENTE', text: 'CLIENTE' }
                );
                
                responsables.forEach(responsable => {
                    options.push({
                        value: responsable.responsable,
                        text: responsable.responsable
                    });
                });
                return options;
            },
        }
    }
</script>