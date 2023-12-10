<template>
    <div>
        <b-row>
            <b-col>
                <!-- PAGINACIÓN -->
                <b-pagination
                    v-model="currentPage"
                    :total-rows="libros.length"
                    :per-page="perPage"
                    aria-controls="my-table"
                    ></b-pagination>
            </b-col>
            <b-col>
                <b-input placeholder="BUSCAR LIBRO"
                    style="text-transform:uppercase;"
                    v-model="queryTitulo"
                    @keyup="getLibro()"
                ></b-input>
            </b-col>
        </b-row>
        <div v-if="!load">
            <b-table id="my-table" :per-page="perPage" :current-page="currentPage" 
                :items="libros" :fields="fields">
                <template v-slot:cell(index)="row">{{ row.index + 1 }}</template>
                <template #thead-top="row">
                    <th colspan="5"></th>
                    <th colspan="2">{{ sistema_1 }}</th>
                    <th colspan="2">{{ sistema_2 }}</th>
                    <th colspan="2">TODO</th>
                </template>
            </b-table>
        </div>
        <load-component v-else></load-component>
    </div>
</template>

<script>
import LoadComponent from '../cortes/partials/LoadComponent.vue';
export default {
  components: { LoadComponent },
    data(){
        return {
            load: false,
            libros: [],
            fields: [
                {key: 'index', label: 'N.'},
                {key: 'type', label: 'tipo'},
                'ISBN', 'titulo', 'editorial',
                {key: 'piezas_1', label: `Piezas`},
                {key: 'defectuosos_1', label: `Defectuosos`},
                {key: 'piezas_2', label: `Piezas`},
                {key: 'defectuosos_2', label: `Defectuosos`},
                'total_piezas', 'total_defectuosos'
            ],
            sistema_1: null,
            sistema_2: null,
            perPage: 20,
            currentPage: 1,
            queryTitulo: null
        }
    },
    created: function (){
        this.getResults();
    },
    methods: {
        getResults(){
            if(this.queryTitulo == null) this.getLibros();
            else this.getLibro();
        },
        // OBTENER TODOS LOS LIBROS DE AMBOS SISTEMAS
        getLibros(){
            this.load = true;
            axios.get(`/libro/all_list`).then(response => {
                this.libros = response.data.libros;
                this.sistema_1 = response.data.sistema_1;
                this.sistema_2 = response.data.sistema_2;
                this.load = false;   
            }).catch(error => {
                this.load = false;
            });
        },
        getLibro(){
            this.load = true;
            axios.get(`/libro/all_libro`, {params: {titulo: this.queryTitulo}}).then(response => {
                this.libros = response.data;
                this.load = false;
            }).catch(error => {
                this.load = false;
                this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
            });
        }
    }
}
</script>

<style>

</style>