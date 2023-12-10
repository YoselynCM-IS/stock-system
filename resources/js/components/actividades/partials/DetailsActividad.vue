<template>
    <div>
        <!-- <b-row class="mb-2">
            <b-col></b-col>
            <b-col sm="2">
                <b-button v-if="actividad.estado == 'completado' && (role_id == 1 || role_id == 6)"
                    variant="dark" pill :href="`/information/actividades/download/${actividad.id}`">
                    <i class="fa fa-download"></i> Descargar
                </b-button>
            </b-col>
        </b-row> -->
        <b-row class="mb-2">
            <b-col sm="3" class="text-right"><label><b>Fecha de creación</b></label></b-col>
            <b-col>{{ actividad.created_at }}</b-col>
        </b-row>
        <b-row class="mb-2">
            <b-col sm="3" class="text-right"><label><b>Nombre de la actividad</b></label></b-col>
            <b-col>{{ actividad.nombre }}</b-col>
        </b-row>
        <b-row class="mb-2">
            <b-col sm="3" class="text-right"><label><b>Tipo</b></label></b-col>
            <b-col>{{ actividad.tipo }}</b-col>
        </b-row>
        <b-row class="mb-2" v-if="actividad.tipo == 'reunion' || actividad.tipo == 'videoconferencia'">
            <b-col sm="3" class="text-right"><label><b>{{setTitulo(actividad.tipo)}}</b></label></b-col>
            <b-col><p v-html="actividad.lugar"></p></b-col>
        </b-row>
        <b-row class="mb-2">
            <b-col sm="3" class="text-right"><label><b>Fecha y hora</b></label></b-col>
            <b-col>{{ actividad.fecha | moment }}</b-col>
        </b-row>
        <b-row class="mb-2">
            <b-col sm="3" class="text-right"><label><b>Recordatorio</b></label></b-col>
            <b-col>{{ actividad.recordatorio | moment }}</b-col>
        </b-row>
        <b-row class="mb-2">
            <b-col sm="3" class="text-right"><label><b>La actividad vence</b></label></b-col>
            <b-col>{{ actividad.marcar_antesde | moment }}</b-col>
        </b-row>
        <b-row class="mb-2">
            <b-col sm="3" class="text-right"><label><b>Descripción</b></label></b-col>
            <b-col><p v-html="actividad.descripcion"></p></b-col>
        </b-row>
        <div v-if="actividad.estado == 'completado'" class="mb-2">
            <hr>
            <b-row>
                <b-col sm="3" class="text-right"><label><b>¿La actividad se completo con exito?</b></label></b-col>
                <b-col>{{ actividad.exitosa }}</b-col>
            </b-row>
        </div>
        <b-row v-if="actividad.observaciones != null">
            <b-col sm="3" class="text-right"><label><b>Observaciones</b></label></b-col>
            <b-col><p v-html="actividad.observaciones"></p></b-col>
        </b-row>
        <div v-if="actividad.clientes.length > 0" class="mb-2">
            <b-row>
                <b-col sm="3" class="text-right"><label><b>Cliente(s)</b></label></b-col>
            </b-row>
            <list-clientes :clientes="actividad.clientes" :quitar="false"></list-clientes>
        </div>
    </div>
</template>

<script>
import setTitulo from '../../../mixins/setTitulo';
import formatFechaActs from '../../../mixins/formatFechaActs';
import ListClientes from './ListClientes.vue';
export default {
  components: { ListClientes },
    props: ['actividad', 'role_id'],
    mixins: [setTitulo, formatFechaActs]
}
</script>

<style>

</style>