<template>
    <div>
        <b-row class="mb-2">
            <b-col sm="8">
                <h6><b>Folio:</b> {{ folio }}</h6>
                <h6><b>Cliente:</b> {{ cliente_name }}</h6>
                <h6 v-if="moneda"><b>Moneda:</b> {{ moneda.codigo }} - {{ moneda.moneda }}</h6>
            </b-col>
            <b-col>
                <b-row v-if="user_name !== null">
                    <b-col class="text-right" sm="4"><h6><b>Creado por:</b></h6></b-col>
                    <b-col>{{ user_name }}</b-col>
                </b-row>
                <b-row>
                    <b-col class="text-right" sm="4"><h6><b>Creado el:</b></h6></b-col>
                    <b-col>{{ created_at | moment }}</b-col>
                </b-row>
                <div v-if="informacion && informacion.length > 5" class="text-right">
                    <b-button v-b-toggle.collapse-1 variant="secondary" size="sm" pill>
                        Información de envió
                    </b-button>
                </div>
            </b-col>
        </b-row>
        <b-collapse id="collapse-1" class="mt-2">
            <b-card bg-variant="light">
                <p v-html="informacion"></p>
            </b-card>
        </b-collapse>
    </div>
</template>

<script>
import moment from '../../../mixins/moment';
export default {
    props: ['folio', 'cliente_name', 'moneda', 'user_name', 'created_at', 'informacion'],
    mixins: [moment]
}
</script>

<style>

</style>