<template>
    <div>
        <h5><b>Remisión {{ remision.id }} - Devolución</b></h5>
        <hr>
        <b-alert show variant="warning">
            <ul>
                <li>En scratch, eliminar libro físico y digital, de lo contrario solo se verá afectado el inventario
                    general.</li>
                <li>Si la existencia es menor a la devolución quedara en negativo, es decir se tendrán que reponer esas
                    unidades.</li>
            </ul>
        </b-alert>
        <b-table :items="remision.fechas" :fields="fields">
            <template v-slot:cell(index)="data">
                {{ data.index + 1 }}
            </template>
            <template v-slot:cell(titulo)="row">
                {{ row.item.libro.titulo }}
                <b-badge v-if="row.item.pack_id != null" variant="info">scratch</b-badge>
            </template>
            <template v-slot:cell(actions)="row">
                <b-button v-if="row.item.libro.type != 'digital' || row.item.pack_id != null" variant="danger" pill
                    size="sm" @click="deleteFecha(row.item, row.index)" :disabled="load">
                    <i class=" fa fa-minus"></i>
                </b-button>
            </template>
            <template v-slot:cell(total)="row">
                ${{ row.item.total | formatNumber }}
            </template>
            <template v-slot:cell(created_at)="row">{{ row.item.created_at | moment }}</template>
        </b-table>
    </div>
</template>

<script>
import formatNumber from '../../mixins/formatNumber';
import sweetAlert from '../../mixins/sweetAlert';
import moment from '../../mixins/moment';
export default {
    props: ['remision'],
    mixins: [formatNumber, moment, sweetAlert],
    data() {
        return {
            fields: [
                { key: 'index', label: 'N.' }, 
                { key: 'titulo', label: 'Titulo' },
                { key: 'libro.piezas', label: 'Existencia' },
                { key: 'unidades', label: 'Unidades' },
                { key: 'defectuosos', label: 'Defectuosos' },
                { key: 'total', label: 'Total' },
                // { key: 'entregado_por', label: 'Entregado por' },
                { key: 'creado_por', label: 'Creado por' },
                { key: 'created_at', label: 'Fecha' },
                { key: 'actions', label: '' },
            ],
            load: false
        }
    },
    methods: {
        // BORRAR ITEM
        deleteFecha(fecha, index){
            this.load = true;
            axios.delete('/devoluciones/delete', { params: { remisione_id: this.remision.id, fecha_id: fecha.id } }).then(response => {
                this.messageAlert('center', 'success', 'La devolución se eliminó correctamente.', null, 'reload');
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