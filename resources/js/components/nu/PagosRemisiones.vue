<template>
    <div>
        <div class="col-md-4">
            <b-form-select v-model="mes" :options="meses" @change="porMes()"></b-form-select>
        </div><br>
        <b-table :items="pagos" :fields="fieldsPagos">
            <template v-slot:cell(index)="row">
                {{ row.index + 1 }}
            </template>
            <template #thead-top="row">
                <tr>
                    <th colspan="2"></th>
                    <th>${{ total_salida | formatNumber }}</th>
                    <th>${{ total_pagos | formatNumber }}</th>
                    <th>${{ total_devolucion | formatNumber }}</th>
                    <th>${{ total_pagar | formatNumber }}</th>
                </tr>
            </template>
        </b-table>
    </div>
</template>

<script>
export default {
    data(){
        return {
            pagos: [],
            fieldsPagos: [
                'index', 'cliente', 'pago', 'created_at'
            ],
            mes: null,
            meses: [],
            inicio: '',
            final: ''
        }
    },
    created: function(){
        this.obtenerPagos();
        this.setMonths();
    },
    methods: {
        setMonths(){
            const now = new Date();
            const m = now.getMonth() + 1;
            if(m < 10) this.mes = '0' + m;
            else{ this.mes = m; }
            var months = [ 
                { value: '01', text: 'ENERO' }, { value: '02', text: 'FEBRERO' },
                { value: '03', text: 'MARZO' }, { value: '04', text: 'ABRIL' },
                { value: '05', text: 'MAYO' }, { value: '06', text: 'JUNIO' },
                { value: '07', text: 'JULIO' }, { value: '08', text: 'AGOSTO' },
                { value: '09', text: 'SEPTIEMBRE' }, { value: '10', text: 'OCTUBRE' },
                { value: '11', text: 'NOVIEMBRE' }, { value: '12', text: 'DICIEMBRE' }
            ];
            this.meses.push({ value: null, text: 'Selecciona una opción', disabled: true});
            this.meses.push({ value: 'TODO', text: 'Mostrar todo' });
            for(var i = 0; i <= now.getMonth(); i++){
                this.meses.push(months[i]);
            }
        },
        obtenerPagos(){
            axios.get('/contador/obtenerPagos').then(response => {
                this.pagos = response.data;
            }).catch(error => {
                
            });
        },
        porMes(){
            axios.get('/contador/pagosFecha', {params: {mes: this.mes}}).then(response => {
                this.pagos = response.data;
            }).catch(error => {
                
            });
        }
    }
}
</script>