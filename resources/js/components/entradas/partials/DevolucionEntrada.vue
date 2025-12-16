<template>
    <div>
        <b-row>
            <b-col>
                <label><b>Folio:</b> {{form.folio}}</label><br>
                <label><b>Editorial:</b> {{form.editorial}}</label>
            </b-col>
            <b-col sm="4">
                <label><b>Imprenta:</b> {{form.imprenta}}</label>
            </b-col>
            <b-col sm="2">
                <b-button variant="success" pill block @click="confirmarDevolucion()"><i class="fa fa-check"></i> Guardar</b-button>
            </b-col>
        </b-row>
        <b-table class="mt-2" :items="form.registros" :fields="fieldsD">
            <template v-slot:cell(index)="row">{{ row.index + 1}}</template>
            <template v-slot:cell(ISBN)="row">{{ row.item.libro.ISBN }}</template>
            <template v-slot:cell(titulo)="row">
                {{ row.item.libro.titulo }}
                <b-badge v-if="row.item.pack_id != null" variant="info">scratch</b-badge>
            </template>
            <template v-slot:cell(costo_unitario)="row">${{ row.item.costo_unitario | formatNumber }}</template>
            <template v-slot:cell(unidades_pendientes)="row">{{ row.item.unidades_pendientes | formatNumber }}</template>
            <template v-slot:cell(total_base)="row">${{ row.item.total_base | formatNumber }}</template>
            <template v-slot:cell(unidades_base)="row">
                <b-input v-if="((row.item.libro.type == 'venta' || row.item.libro.type == 'promocion') && row.item.pack_id == null) ||
                    (row.item.libro.type == 'digital' && row.item.pack_id !== null)" :id="`inpEntDev-${row.index}`"
                    type="number" @change="obtenerSubtotal(row.item, row.index)" v-model="row.item.unidades_base">
                </b-input>
                <label v-if="(row.item.libro.type == 'venta' && row.item.pack_id != null) ||
                        (row.item.libro.type == 'digital' && row.item.codes.length >= 0 && row.item.pack_id == null)">
                    {{ row.item.unidades_base }}
                </label>
            </template>
            <template v-slot:cell(codes)="row">
                <b-button v-if="row.item.libro.type == 'digital' && row.item.codes.length > 0" pill small variant="info"
                    @click="selectCodigos(row.item, row.index)">
                    Códigos
                </b-button>
            </template>
            <template #thead-top="row">
                <tr>
                    <th colspan="5"></th>
                    <th>{{ form.todo_unidades | formatNumber }}</th>
                    <th>${{ form.todo_total | formatNumber }}</th>
                </tr>
            </template>
        </b-table>
        <!-- MODALS -->
        <b-modal ref="modal-confirmarDevolucion" size="xl" title="">
            <template #modal-title><b>Resumen de la devolución</b></template>
            <b-row>
                <b-col>
                    <label><b>Folio:</b> {{form.folio}}</label><br>
                    <label><b>Editorial:</b> {{form.editorial}}</label>
                </b-col>
                <b-col>
                    <label><b>Imprenta:</b> {{form.imprenta}}</label>
                </b-col>
            </b-row>
            <b-table class="mt-2" :items="form.registros" :fields="fieldsD">
                <template v-slot:cell(index)="row">{{ row.index + 1}}</template>
                <template v-slot:cell(ISBN)="row">{{ row.item.libro.ISBN }}</template>
                <template v-slot:cell(titulo)="row">
                    {{ row.item.libro.titulo }}
                    <b-badge v-if="row.item.pack_id != null" variant="info">scratch</b-badge>
                </template>
                <template v-slot:cell(costo_unitario)="row">${{ row.item.costo_unitario | formatNumber }}</template>
                <template v-slot:cell(total_base)="row">${{ row.item.total_base | formatNumber }}</template>
                <template v-slot:cell(unidades_base)="row">{{ row.item.unidades_base | formatNumber }}</template>
                <template v-slot:cell(unidades_pendientes)="row">{{ row.item.unidades_pendientes | formatNumber }}</template>
                <template v-slot:cell(codes)="row"></template>
                <template #thead-top="row">
                    <tr>
                        <th colspan="5"></th>
                        <th>{{ form.todo_unidades | formatNumber }}</th>
                        <th>${{ form.todo_total | formatNumber }}</th>
                    </tr>
                </template>
            </b-table>
            <div slot="modal-footer">
                <b-row>
                    <b-col>
                        <b-alert show variant="info">
                            <i class="fa fa-exclamation-circle"></i> <b>Verificar la devolución.</b> En caso de algún error, modificar antes de presionar <b>Confirmar</b> ya que después no se podrán realizar cambios.
                        </b-alert>
                    </b-col>
                    <b-col sm="2" align="right">
                        <b-button :disabled="load" block pill variant="success" @click="guardarDevolucion()">
                            <i class="fa fa-check"></i> Confirmar
                        </b-button>
                    </b-col>
                </b-row>
            </div>
        </b-modal>
        <!-- MODAL PARA SELECCIONAR CODIGOS -->
        <b-modal id="modal-select-codes" title="Seleccionar códigos" hide-footer>
            <b-table :items="codes" :fields="fieldsCodes" responsive :select-mode="selectMode" ref="selectableTable"
                selectable @row-selected="onRowSelected">
                <template v-slot:cell(index)="row">
                    {{ row.index + 1 }}
                </template>
            </b-table>
            <div class="text-right">
                <b-button :disabled="selected.length == 0" variant="success" pill @click="guardarCodes()">
                    <i class="fa fa-check"></i> Guardar
                </b-button>
            </div>
        </b-modal>
    </div>
</template>

<script>
import formatNumber from './../../../mixins/formatNumber';
import toast from './../../../mixins/toast';
import sweetAlert from '../../../mixins/sweetAlert';
export default {
    props: ['form'],
    mixins: [formatNumber,toast, sweetAlert],
    data(){
        return {
            fieldsD: [
                {key: 'index', label: 'N.'}, 
                {key: 'ISBN', label: 'ISBN'}, 
                {key: 'titulo', label: 'Libro'}, 
                {key: 'costo_unitario', label: 'Costo unitario'}, 
                {key: 'unidades_pendientes', label: 'Unidades pendientes'}, 
                {key: 'unidades_base', label: 'Unidades devolución'}, 
                {key: 'total_base', label: 'Total'},
                {key: 'codes', label: ''}
            ],
            load: false,
            position: 0,
            codes: [],
            selectMode: 'multi',
            selected: [],
            fieldsCodes: [
                {key: 'index', label: 'N.'}, 
                {key: 'codigo', label: 'Código'}, 
            ],
        }
    },
    methods: {
        // GUARDAR DEVOLUCION
        confirmarDevolucion(){
            // if(this.form.todo_total > 0 || (this.form.editorial == 'MAJESTIC EDUCATION' && this.form.todo_total == 0)){
            if(this.form.todo_total > 0){
                this.$refs['modal-confirmarDevolucion'].show();
            } else {
                this.makeToast('warning', 'El total debe ser mayor a cero.');
            }
        },
        obtenerSubtotal(registro, i) {
            this.check_add(registro, i, registro.pack_id == null ? registro.libro.piezas:registro.pack.piezas);
        },
        check_add(registro, i, total_piezas) {
            if (registro.unidades_base <= total_piezas) {
                if (registro.unidades_base >= 0) {
                    if (registro.unidades_base > registro.unidades_pendientes) {
                        this.makeToast('warning', 'Las unidades son mayor a las unidades pendientes');
                        this.to_zero(i);
                    } else {
                        this.form.registros[i].total_base = registro.unidades_base * registro.costo_unitario;
                        // if (i + 1 < this.form.registros.length) {
                        //     document.getElementById('inpEntDev-' + (i + 1)).focus();
                        //     document.getElementById('inpEntDev-' + (i + 1)).select();
                        // }
                    }
                }
                else {
                    this.makeToast('warning', 'Unidades invalidas');
                    this.to_zero(i);
                }
            } else {
                this.makeToast('warning', `Hay ${total_piezas} en existencia`);
                this.to_zero(i);
            } 
            this.set_search(registro);
            this.acumularFinal();   
        },
        set_search(registro) {
            if (registro.pack_id != null) {
                let pos = this.form.registros.findIndex(r => {
                    if (r.pack_id == registro.pack_id && r.libro.type == 'venta')
                        return r;
                });
                let fisico = this.form.registros[pos];
                fisico.unidades_base = registro.unidades_base;
                fisico.total_base = fisico.unidades_base * fisico.costo_unitario;
            }
        },
        to_zero(i){
            this.form.registros[i].unidades_base = 0;
            this.form.registros[i].total_base = 0;
        },
        acumularFinal(){
            this.form.todo_unidades = 0;
            this.form.todo_total = 0;
            this.form.registros.forEach(registro => {
                this.form.todo_unidades += parseInt(registro.unidades_base);
                this.form.todo_total += parseFloat(registro.total_base);
            });
        },
        // CONFIRMAR DEVOLUCION
        guardarDevolucion(){
            this.load = true;
            axios.post('/entradas/devolucion', this.form).then(response => {
                this.messageAlert('center', 'success', 'La devolución se guardo correctamente.', null, 'reload');
                this.load = false; 
            }).catch(error => {
                this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                this.load = false;
            });
        },
        selectCodigos(devolucion, i){
            this.position = i;
            this.form.registros[this.position].unidades_base = 0;
            this.form.registros[this.position].total_base = 0;
            this.codes = devolucion.codes;
            this.acumularFinal();
            this.$bvModal.show('modal-select-codes');
        },
        onRowSelected(items) {
            this.selected = items
        },
        guardarCodes(){
            this.form.registros[this.position].code_registro = [];
            let devolucion = this.form.registros[this.position];
            let unidades_base = 0;
            this.selected.forEach(e => {
                devolucion.code_registro.push(e.id);
                unidades_base++;
            });
            devolucion.total_base = devolucion.costo_unitario * unidades_base;
            devolucion.unidades_base = unidades_base;
            this.acumularFinal();
            this.$bvModal.hide('modal-select-codes');
        }
    }
}
</script>

<style>

</style>