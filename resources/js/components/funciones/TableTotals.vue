<template>
    <div>
        <table class="table table-bordered mt-3 mb-3 text-center">
            <thead :class="`table-${variant}`">
                <tr>
                    <th scope="col" style="width: 25%;">Total</th>
                    <th scope="col" style="width: 25%;">Devolución</th>
                    <th scope="col" style="width: 25%;">Pagos</th>
                    <th scope="col" style="width: 25%;">Pagar</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>${{ dato.total | formatNumber }}</td>
                    <td>${{ dato.total_devolucion | formatNumber }}</td>
                    <td>${{ dato.total_pagos | formatNumber }}</td>
                    <td>${{ dato.total_pagar | formatNumber }}</td>
                </tr>
                <tr v-if="favor && dato.total_favor > 0"
                    class="table-secondary">
                    <td scope="col" colspan="2" class="text-left">
                        <b>Temporada {{ dato.corte_id_favor }}</b>
                        <br><i style="size: 10px">Saldo a favor</i>
                    </td>
                    <td>${{ dato.total_favor | formatNumber }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
import formatNumber from '../../mixins/formatNumber';
export default {
    props: ['dato','variant','favor'],
    mixins: [formatNumber]
}
</script>

<style>

</style>