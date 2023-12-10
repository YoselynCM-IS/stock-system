<template>
    <div>
        <!-- INFORMACION DE LA REMISION -->
        <b-row class="mb-3">
            <b-col sm="8">
                <label><b>Cliente:</b> {{ cliente_name }}</label><br>
                <label><b>Fecha de creación:</b> {{ remision.fecha_creacion }}</label>
            </b-col>
            <b-col sm="4">
                <label><b>Fecha de entrega:</b> {{ remision.fecha_entrega }}</label><br>
                <label v-if="remision.responsable !== null && remision.responsable !== 'NA'">
                    <b>Responsable de entrega:</b> {{ remision.responsable }}
                </label>
            </b-col>
        </b-row>
    </div>
</template>

<script>
export default {
    props: ['remision', 'cliente_name']
}
</script>

<style>

</style>