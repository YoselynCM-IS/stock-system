<template>
    <div>
        <b-table v-if="!load" :items="reportes.data" :fields="fields"
            :select-mode="selectMode" responsive="sm" ref="selectableTable" selectable @row-selected="onRowSelected">
            <template v-slot:cell(index)="row">{{ row.index + 1 }}</template>
            <!-- <template v-slot:cell(comentario)="row">
                <b-button pill size="sm" variant="info" @click="getComentario(row.item)">
                    <i class="fa fa-comment-o"></i>
                </b-button>
            </template> -->
        </b-table>
        <load-component v-else></load-component>
    </div>
</template>

<script>
import LoadComponent from '../../funciones/LoadComponent.vue'
export default {
    props: ['reportes', 'load'],
    components: { LoadComponent },
    data(){
        return {
            fields: [
                {key: 'index', label: 'N.'},
                {key: 'created_at', label: 'Registrado el'},
                {key: 'user.name', label: 'Registrado por'},
                {key: 'reporte', label: 'Reporte'},
                // {key: 'comentario', label: 'Comentario'}
            ],
            selectMode: 'multi',
            selected: [],
        }
    },
    methods: {
        onRowSelected(items) {
            console.log(items);
            this.selected = items
        },
    }
    
}
</script>

<style>

</style>