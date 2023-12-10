<template>
    <div>
        <check-connection-component></check-connection-component>
        
        <div v-if="!view_detalles">
            <b-row>
                <b-col sm="5">
                    <b-row>
                        <b-col sm="3" class="text-right"><label>Editorial</label></b-col>
                        <b-col sm="7">
                            <b-form-select v-model="queryEMov" :options="options" @change="editorialMov()"></b-form-select>
                        </b-col>
                    </b-row>
                </b-col>
                <b-col sm="5">
                    <b-row>
                        <b-col sm="3" class="text-right"><label>Mes</label></b-col>
                        <b-col sm="7">
                            <b-form-select v-model="mes" :options="meses" @change="porFecha()"></b-form-select>
                        </b-col>
                    </b-row>
                </b-col>
                <b-col>
                    <b-button variant="dark" :href="`/download_movmonto/${queryEMov}/${mes}`">
                        <i class="fa fa-download"></i> Descargar
                    </b-button>
                </b-col>
            </b-row><br>
            <div v-if="movimientos.length > 0">
                <b-table
                    id="table-mov"
                    striped
                    responsive
                    :per-page="perPage"
                    :current-page="currentPage"
                    :items="movimientos"
                    :fields="fieldsMov">
                    <template v-slot:cell(entradas)="row">${{ row.item.entradas | formatNumber }}</template>
                    <template v-slot:cell(devoluciones)="row">${{ row.item.devoluciones | formatNumber }}</template>
                    <template v-slot:cell(total_entrada)="row">${{ row.item.total_entrada | formatNumber }}</template>
                    <template v-slot:cell(entdevoluciones)="row">${{ row.item.entdevoluciones | formatNumber }}</template>
                    <template v-slot:cell(remisiones)="row">${{ row.item.remisiones | formatNumber }}</template>
                    <template v-slot:cell(notas)="row">${{ row.item.notas | formatNumber }}</template>
                    <template v-slot:cell(total_salida)="row">${{ row.item.total_salida | formatNumber }}</template>
                    <template v-slot:cell(details)="row">
                        <b-button variant="info" @click="detailsMonto(row.item)">Detalles</b-button>
                    </template>
                    <template v-slot:cell(total)="row">
                        ${{ row.item.total | formatNumber }}
                    </template>
                    <template #thead-top="row">
                        <tr>
                            <th colspan="1"></th>
                            <th colspan="3" class="table-secondary text-center">ENTRADAS</th>
                            <th colspan="4" class="table-primary text-center">SALIDAS</th>
                            <!-- <th></th> -->
                        </tr>
                    </template>
                </b-table>
                <b-pagination
                    v-model="currentPage"
                    :total-rows="movimientos.length"
                    :per-page="perPage"
                    aria-controls="table-mov">
                </b-pagination>
            </div>
            <b-alert v-else show variant="secondary">
                <i class="fa fa-warning"></i> No se encontraron registros.
            </b-alert>
        </div>
        <div v-else>
            <b-row>
                <b-col><h4><b>{{ libro.libro }}</b></h4></b-col>
                <b-col class="text-right" sm="2">
                    <b-button variant="secondary" v-on:click="view_detalles = false"><i class="fa fa-arrow-circle-left"></i> Regresar</b-button>
                </b-col>
            </b-row>
            <h5><b>ENTRADAS</b></h5>
            <div v-if="libro.entradas.length > 0">
                <h6><b>Entradas</b></h6>
                <b-table :items="libro.entradas">
                    <template v-slot:cell(entradas)="row">
                        ${{ row.item.entradas | formatNumber }}
                    </template>
                    <template #thead-top="row">
                        <tr>
                            <th colspan="2"></th>
                            <th>${{ total_entrada | formatNumber }}</th>
                        </tr>
                    </template>
                </b-table>
            </div>
            <div v-if="libro.devoluciones.length > 0">
                <h6><b>Devolución (Remisiones)</b></h6>
                <b-table :items="libro.devoluciones">
                    <template v-slot:cell(devoluciones)="row">
                        ${{ row.item.devoluciones | formatNumber }}
                    </template>
                    <template #thead-top="row">
                        <tr>
                            <th colspan="2"></th>
                            <th>${{ total_devolucion | formatNumber }}</th>
                        </tr>
                    </template>
                </b-table>
            </div>
            <hr>
            <h5><b>SALIDAS</b></h5>
            <div v-if="libro.entdevoluciones.length > 0">
                <h6><b>Devolución (Entradas)</b></h6>
                <b-table :items="libro.entdevoluciones">
                    <template v-slot:cell(entdevoluciones)="row">
                        ${{ row.item.entdevoluciones | formatNumber }}
                    </template>
                    <template #thead-top="row">
                        <tr>
                            <th colspan="2"></th>
                            <th>${{ total_entdevolucion | formatNumber }}</th>
                        </tr>
                    </template>
                </b-table>
            </div>
            <div v-if="libro.remisiones.length > 0">
                <h6><b>Remisiones</b></h6>
                <b-table :items="libro.remisiones">
                    <template v-slot:cell(remisiones)="row">
                        ${{ row.item.remisiones | formatNumber }}
                    </template>
                    <template #thead-top="row">
                        <tr>
                            <th colspan="2"></th>
                            <th>${{ total_remision | formatNumber }}</th>
                        </tr>
                    </template>
                </b-table>
            </div>
            <div v-if="libro.notas.length > 0">
                <h6><b>Notas</b></h6>
                <b-table :items="libro.notas">
                    <template v-slot:cell(notas)="row">
                        ${{ row.item.notas | formatNumber }}
                    </template>
                    <template #thead-top="row">
                        <tr>
                            <th colspan="2"></th>
                            <th>${{ total_nota | formatNumber }}</th>
                        </tr>
                    </template>
                </b-table>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: ['editoriales'],
    data(){
        return {
            view_detalles: false,
            options: [],
            queryEMov: 'TODO',
            movimientos: [],
            fieldsMov: [
                'titulo', 'entradas',
                {key: 'devoluciones', label: 'Devolución (Remisiones)'},
                {key: 'total_entrada', label: 'TOTAL (Entrada)', variant: 'secondary'},
                {key: 'entdevoluciones', label: 'Devolución (Entradas)'},
                'remisiones', 'notas', 
                {key: 'total_salida', label: 'TOTAL (Salida)', variant: 'primary'},
                'total',
                {key: 'details', label: 'Detalles'},
            ],
            perPage: 10,
            currentPage: 1,
            libro: {},
            fieldsEDepositos: [
                {key: 'index', label: '#'}, 'pago',
                {key: 'created_at', label: 'Fecha'},
            ],
            mes: 'TODO',
            meses: [
                { value: 'TODO', text: 'TODO' },
                { value: '01', text: 'Enero' }, { value: '02', text: 'Febrero' },
                { value: '03', text: 'Marzo' }, { value: '04', text: 'Abril' },
                { value: '05', text: 'Mayo' }, { value: '06', text: 'Junio' },
                { value: '07', text: 'Julio' }, { value: '08', text: 'Agosto' },
                { value: '09', text: 'Septiembre' }, { value: '10', text: 'Octubre' },
                { value: '11', text: 'Noviembre' }, { value: '12', text: 'Diciembre' }
            ],
            total_entrada: 0,
            total_devolucion: 0,
            total_remision: 0,
            total_nota: 0,
            total_entdevolucion: 0,
        }
    },
    filters: {
        moment: function (date) {
            return moment(date).format('DD-MM-YYYY');
        },
        formatNumber: function (value) {
            return numeral(value).format("0,0[.]00"); 
        }
    },
    mounted: function(){
        this.assign_editorial();
        this.movimientosLibros();
    },
    methods: {
        assign_editorial(){
            this.options.push({
                value: 'TODO',
                text: 'MOSTRAR TODO'
            });
            this.editoriales.forEach(editorial => {
                this.options.push({
                    value: editorial.editorial,
                    text: editorial.editorial
                });
            });
        },
        movimientosLibros(){
            axios.get('/all_movmonto').then(response => {
                this.movimientos = response.data;
                this.queryEMov = 'TODO';
            }).catch(error => {
                this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
            });
        },
        editorialMov(){
            axios.get('/editorial_movmonto', {params: {editorial: this.queryEMov}}).then(response => {
                this.movimientos = response.data;
                this.mes = 'TODO';
            }).catch(error => {
                this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
            });
        },
        detailsMonto(libro){
            axios.get('/detalles_monto', {params: {titulo: libro.titulo}}).then(response => {
                this.libro = response.data;
                this.acum_totales();
                this.view_detalles = true;
            }).catch(error => {
                // this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
            });
        },
        acum_totales(){
            this.total_entrada = 0;
            this.total_devolucion = 0;
            this.total_remision = 0;
            this.total_nota = 0;
            this.total_entdevolucion = 0;
            this.libro.entradas.forEach(entrada => {
                this.total_entrada += entrada.entradas; 
            });
            this.libro.devoluciones.forEach(devolucion => {
                this.total_devolucion += devolucion.devoluciones; 
            });
            this.libro.remisiones.forEach(remision => {
                this.total_remision += remision.remisiones; 
            });
            this.libro.notas.forEach(nota => {
                this.total_nota += nota.notas; 
            });
            this.libro.entdevoluciones.forEach(entdevolucion => {
                this.total_entdevolucion += entdevolucion.entdevoluciones; 
            });
        },
        porFecha(){
            if(this.mes === 'TODO'){
                this.movimientosLibros();
            } else {
                axios.get('/fecha_movmonto', {params: {editorial: this.queryEMov, mes: this.mes}}).then(response => {
                    this.movimientos = response.data;
                }).catch(error => {
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                });
            }
        }
    }
}
</script>