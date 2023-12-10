<template>
    <div>
        <table class="table table-bordered mt-3 mb-3 text-center">
             <thead :class="`table-${variant}`">
                <tr>
                    <th scope="col">Total</th>
                    <th scope="col">Devolución</th>
                    <th scope="col">Pagos</th>
                    <th scope="col">Pagar</th>
                    <th v-if="favor && dato.total_favor > 0" scope="col">A favor</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>${{ dato.total | formatNumber }}</td>
                    <td>${{ dato.total_devolucion | formatNumber }}</td>
                    <td>${{ dato.total_pagos | formatNumber }}</td>
                    <td>${{ dato.total_pagar | formatNumber }}</td>
                    <td v-if="favor && dato.total_favor > 0">
                        ${{ dato.total_favor | formatNumber }} <br>
                        Temp {{ dato.corte_id_favor }}
                    </td>
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