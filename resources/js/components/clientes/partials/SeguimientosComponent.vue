<template>
    <div>
        <div v-if="!load">
            <b-table v-if="seguimientos.length > 0" :items="seguimientos" :fields="fields">
                <template v-slot:cell(index)="row">
                    {{ row.index + 1 }}
                </template>
                <template #cell(show_details)="row">
                    <b-button size="sm" @click="row.toggleDetails" pill variant="info">
                        <i :class="`fa fa-angle-double-${row.detailsShowing ? `up`:`down`}`"></i>
                    </b-button>
                </template>
                <template #row-details="row">
                    <b-card>
                        <b-list-group flush>
                            <b-list-group-item v-if="row.item.duracion !== '0 horas 0 minutos 0 segundos'">
                                <b-row>
                                    <b-col sm="3" class="text-right"><b>Duración:</b></b-col>
                                    <b-col>{{ row.item.duracion }}</b-col>
                                </b-row>
                            </b-list-group-item>
                            <b-list-group-item>
                                <b-row>
                                    <b-col sm="3" class="text-right"><b>Comentario:</b></b-col>
                                    <b-col>{{ row.item.comentario }}</b-col>
                                </b-row>
                            </b-list-group-item>
                            <b-list-group-item>
                                <b-row>
                                    <b-col sm="3" class="text-right"><b>Registrado por:</b></b-col>
                                    <b-col>{{ row.item.user.name }}</b-col>
                                </b-row>
                            </b-list-group-item>
                            <b-list-group-item>
                                <b-row>
                                    <b-col sm="3" class="text-right"><b>Registrado el:</b></b-col>
                                    <b-col>{{ row.item.created_at }}</b-col>
                                </b-row>
                            </b-list-group-item>
                        </b-list-group>
                    </b-card>
                </template>
            </b-table>
            <no-registros-component v-else></no-registros-component>
        </div>
        <load-component v-else></load-component>
    </div>
</template>

<script>
import LoadComponent from '../../cortes/partials/LoadComponent.vue';
import NoRegistrosComponent from '../../funciones/NoRegistrosComponent.vue';
export default {
  components: { LoadComponent, NoRegistrosComponent },
    props: ['cliente_id'],
    data(){
        return {
            load: false,
            seguimientos: [],
            fields: [
                {key: 'index', label: 'N.'},
                {key: 'fecha_hora', label: 'Fecha'},
                {key: 'tipo', label: 'Registro'},
                {key: 'situacion', label: 'Tipo'},
                {key: 'respuesta', label: 'Respuesta'},
                {key: 'show_details', label: ''}
            ]
        }
    },
    created: function(){
        this.getSeguimiento();
    },
    methods: {
        getSeguimiento(){
            this.load = true;
            axios.get('/clientes/get_seguimiento', {params: {cliente_id: this.cliente_id}}).then(response => {
                this.seguimientos = response.data;
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