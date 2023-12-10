<template>
    <div>
        <b-row class="mt-2">
            <b-col><b>Proveedor:</b> {{ order.provider }}</b-col>
            <b-col sm="4">
                <label v-if="order.creado_por !== null"><b>Creado por:</b> {{ order.creado_por }}</label>
            </b-col>
        </b-row>
        <b-row class="mb-3">
            <b-col><b>Para:</b> {{ order.destination }}</b-col>
            <b-col sm="4"><b>Creado el:</b> {{ order.date | moment }}</b-col>
        </b-row>
    </div>
</template>

<script>
import moment from '../../../mixins/moment';
export default {
    props: ['order'],
    mixins: [moment]
}
</script>

<style>

</style>