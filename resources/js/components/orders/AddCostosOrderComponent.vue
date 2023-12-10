<template>
    <div>
        <b-row class="mt-2">
            <b-col>
                <datos-order :order="order"></datos-order>
            </b-col>
            <b-col sm="2">
                <div v-if="order.tipo == 'digitales'">
                    <b><i>¿Enviar a almacén?</i></b>
                    <b-form-radio-group
                        v-model="form.almacen"
                        :options="options"
                        class="mb-3"
                        value-field="item"
                        text-field="name"
                        disabled-field="notEnabled"
                    ></b-form-radio-group>
                </div>
            </b-col>
            <b-col sm="2" class="text-right">
                <b-button variant="success" block pill @click="saveCostos()"
                    :disabled="(load || form.total_bill == 0)">
                    <i class="fa fa-check"></i> Guardar
                </b-button>
            </b-col>
        </b-row>
        <b-table :items="form.elements" :fields="fieldsRegistros">
            <template v-slot:cell(index)="data">
                {{ data.index + 1 }}
            </template>
            <template v-slot:cell(quantity)="data">
                {{ data.item.quantity | formatNumber }}
            </template>
            <template v-slot:cell(unit_price)="data">
                <b-form-input :id="`inpOrd-${data.index}`"
                    @change="guardarUnitPrice(data.item, data.index)"
                    v-model="data.item.unit_price" 
                    type="number" required :disabled="load">
                </b-form-input>
            </template>
            <template v-slot:cell(total)="data">
                ${{ data.item.total | formatNumber }}
            </template>
            <template #thead-top="row">
                <tr class="mt-5">
                    <th colspan="5"></th>
                    <th class="text-right"><b>Total Factura</b></th>
                    <th>
                        <b>${{ form.total_bill | formatNumber }}</b>
                    </th>
                    <th></th>
                </tr>
            </template>
        </b-table>
    </div>
</template>

<script>
import DatosOrder from './partials/DatosOrder.vue';
import formatNumber from '../../mixins/formatNumber';
import toast from '../../mixins/toast';
import sweetAlert from '../../mixins/sweetAlert';
export default {
    components: { DatosOrder },
    mixins: [formatNumber, toast, sweetAlert],
    props: ['order'],
    data(){
        return {
            form: {
                id: null,
                elements: [],
                almacen: 'SI',
                total_bill: 0
            },
            fieldsRegistros: [
                {label: 'N.', key: 'index'},
                {label: 'ISBN', key: 'libro.ISBN'},
                {label: 'Titulo', key: 'libro.titulo'},
                {label: '', key: 'tipo'},
                {label: 'Cantidad', key: 'quantity'},
                {label: 'Precio unitario', key: 'unit_price'},
                {label: 'Total', key: 'total'}
            ],
            load: false,
            options: [
                { item: 'SI', name: 'SI' },
                { item: 'NO', name: 'NO' },
            ]
        }
    },
    created: function(){
        this.assign_datos_order();
    },
    methods: {
        assign_datos_order(){
            this.form.id = this.order.id;
            this.order.elements.forEach(element => {
                let d = {
                    id: element.id,
                    order_id: element.order_id,
                    libro_id: element.libro_id,
                    tipo: element.tipo,
                    quantity: element.quantity,
                    unit_price: 0,
                    total: 0,
                    libro: {
                        id: element.libro.id,
                        ISBN: element.libro.ISBN,
                        titulo: element.libro.titulo
                    }
                };
                this.form.elements.push(d);
            });
        },
        guardarUnitPrice(element, position){
            if(element.unit_price > 0){
                this.form.elements[position].total = element.unit_price * element.quantity;
                if(position + 1 < this.form.elements.length){
                    document.getElementById('inpOrd-'+(position+1)).focus();
                    document.getElementById('inpOrd-'+(position+1)).select();
                }
            } else {
                this.makeToast('warning', 'El precio unitario debe ser mayor a 0.');
                this.form.elements[position].unit_price = 0;
                this.form.elements[position].total = 0;
            }

            this.form.total_bill = 0;
            this.form.elements.forEach(element => {
                this.form.total_bill += element.total;
            });
        },
        saveCostos(){
            if(this.check_elements() > 0){
                this.load = true;
                axios.put('/order/add_costo', this.form).then(response => {
                    this.messageAlert('center', 'success', 'Los costos se agregaron correctamente.', null, 'reload');
                    this.load = false;
                }).catch(error => {
                    this.load = false;
                });
            } else {
                this.makeToast('warning', 'El precio unitario debe ser mayor a 0.');
            }
        },
        check_elements(){
            let count_check = 0;
            this.form.elements.forEach(element => {
                if(element.total > 0) count_check++;
            });
            return count_check;
        }
    }
}
</script>

<style>

</style>