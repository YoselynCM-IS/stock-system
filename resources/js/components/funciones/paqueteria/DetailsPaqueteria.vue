<template>
    <div>
        <b-row>
            <b-col sm="3" class="text-right"><b>Destinatario:</b></b-col>
            <b-col>{{ tipo.paqueteria.destinatario.destinatario }}</b-col>
        </b-row>
        <b-row>
            <b-col sm="3" class="text-right"><b>Dirección:</b></b-col>
            <b-col>{{ tipo.paqueteria.destinatario.direccion }}</b-col>
        </b-row>
        <b-row>
            <b-col sm="3" class="text-right"><b>RFC:</b></b-col>
            <b-col>{{ tipo.paqueteria.destinatario.rfc }}</b-col>
        </b-row>
        <b-row>
            <b-col sm="3" class="text-right"><b>Teléfono:</b></b-col>
            <b-col>{{ tipo.paqueteria.destinatario.telefono }}</b-col>
        </b-row>
        <b-row>
            <b-col sm="3" class="text-right"><b>Régimen fiscal:</b></b-col>
            <b-col>{{ setRF(tipo.paqueteria.destinatario.regimen_fiscal) }}</b-col>
        </b-row><hr>
        <b-row>
            <b-col sm="3" class="text-right"><b>Nombre de la paquetería:</b></b-col>
            <b-col>{{ tipo.paqueteria.paqueteria }}</b-col>
        </b-row>
        <b-row>
            <b-col sm="3" class="text-right"><b>Tipo de envió:</b></b-col>
            <b-col>{{ tipo.paqueteria.tipo_envio }}</b-col>
        </b-row>
        <b-row>
            <b-col sm="3" class="text-right"><b>Fecha de envió:</b></b-col>
            <b-col>{{ tipo.paqueteria.fecha_envio }}</b-col>
        </b-row>
        <b-row>
            <b-col sm="3" class="text-right"><b>Costo de envió:</b></b-col>
            <b-col>${{ tipo.paqueteria.precio | formatNumber }}</b-col>
        </b-row>
        <b-row>
            <b-col sm="3" class="text-right"><b>Número de guía:</b></b-col>
            <b-col>{{ tipo.paqueteria.guia }}</b-col>
        </b-row>
        <b-row v-if="tipo.paqueteria.public_url !== null">
            <b-col sm="3" class="text-right"><b>Comprobante guía:</b></b-col>
            <b-col>
                <b-button pill size="sm" variant="dark" 
                    :href="tipo.paqueteria.public_url" target="_blank">
                    <i class="fa fa-eye"></i>
                </b-button>
            </b-col>
        </b-row>
    </div>
</template>

<script>
import formatNumber from '../../../mixins/formatNumber'

export default {
    props: ['tipo'],
    mixins: [formatNumber],
    methods: {
        setRF(regimen_fiscal) {
            if (regimen_fiscal == 'pf con ae') return 'Persona física sin actividad empresarial';
            if (regimen_fiscal == 'pf sin ae') return 'Persona física con actividad empresarial';
            if (regimen_fiscal == 'pm de lg') return 'Ley general de personas morales';
            if (regimen_fiscal == 'pm sin fl') return 'Personas morales sin fines lucrativos';
        },
    }
}
</script>

<style>

</style>