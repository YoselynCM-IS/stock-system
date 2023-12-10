<template>
    <div>
        <b-row class="mb-3">
            <b-col><h5><b>Scratch</b></h5></b-col>
            <b-col sm="3">
                <b-button v-if="role_id == 6"
                    variant="success" pill block @click="addPack()"
                    :disabled="load">
                    <i class="fa fa-plus-circle"></i> Agregar paquete
                </b-button>
            </b-col>
        </b-row>
        <b-modal ref="modal-addPack" title="Agregar paquete" hide-footer>
            <form @submit="onSubmit">
                <busqueda-libros text="Libro físico" :type="'venta'" :results="rFisicos"
                    @searchLibros="searchLibros" @libroSelect="libroSelect"></busqueda-libros>
                <busqueda-libros text="Libro digital" :type="'digital'" :results="rDigitales"
                    @searchLibros="searchLibros" @libroSelect="libroSelect"></busqueda-libros>
                <b-button :disabled="load" variant="success" type="submit" pill>
                    <i v-if="!load" class="fa fa-plus-circle"></i>
                    <b-spinner v-else type="grow" small></b-spinner>
                    {{ !load ? 'Guardar' : 'Cargando' }}
                </b-button>
            </form>
        </b-modal>
    </div>
</template>

<script>
import BusquedaLibros from './partials/BusquedaLibros.vue';
import sweetAlert from '../../mixins/sweetAlert';
export default {
    props: ['role_id'],
    components: { BusquedaLibros },
    mixins: [sweetAlert],
    data() {
        return {
            form: {
                libro_fisico: null,
                libro_digital: null
            },
            load: false,
            rDigitales: [],
            rFisicos: []
        }
    },
    methods: {
        // AGREGAR PAQUETE
        addPack() {
            this.form.libro_fisico = null;
            this.form.libro_digital = null;
            this.rDigitales = [];
            this.rFisicos = [];
            this.$refs['modal-addPack'].show();
        },
        searchLibros(query, type) {
            this.load = true;
            axios.get(`/libro/by_editorial_digital`, { params: { titulo: query, editorial: 'MAJESTIC EDUCATION', type: type } }).then(response => {
                if (type == 'digital') this.rDigitales = response.data;
                if (type == 'venta') this.rFisicos = response.data;
                this.load = false;
            }).catch(error => {
                this.load = false;
            });
        },
        libroSelect(libro) {
            if (libro.type == 'digital') {
                this.form.libro_digital = libro.id;
                this.rDigitales = [];
            }
            if (libro.type == 'venta') {
                this.form.libro_fisico = libro.id;
                this.rFisicos = [];
            }
        },
        onSubmit(e) {
            e.preventDefault();
            this.load = true;
            axios.post('/libro/save_pack', this.form).then(response => {
                if (response.data)
                    this.messageAlert('center', 'success', 'El paquete se guardó correctamente.', null, 'reload');
                else
                    this.messageAlert('center', 'warning', 'El paquete ya existe.', null, 'info');
                this.load = false;
            }).catch(error => {
                this.load = false;
            });
        }
    }
}
</script>

<style>

</style>