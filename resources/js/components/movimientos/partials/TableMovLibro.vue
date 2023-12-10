<template>
    <div>
        <div v-if="registros.length > 0">
            <h6><b>{{ titulo }}</b></h6>
            <b-table :items="registros" :fields="fields">
                <template v-slot:cell(created_at)="row">
                    {{ row.item.created_at | moment }}
                </template>
            </b-table>
        </div>
    </div>
</template>

<script>
import moment from './../../../mixins/moment';
export default {
    props: ['titulo', 'registros', 'label'],
    mixins: [moment],
    data(){
        return {
            fields: [
                { key: 'folio', label: this.label },
                { key: 'titulo', label: 'Titulo' },
                { key: 'unidades', label: 'Unidades' },
                { key: 'created_at', label: 'Fecha' }
            ]
        }
    }
}
</script>

<style>

</style>