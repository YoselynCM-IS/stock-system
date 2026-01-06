<template>
    <div>
        <b-alert v-if="errorFormat" show variant="warning">
            <i class="fa fa-info-circle"></i> Formato de archivo no permitido
        </b-alert>
        <form @submit="onSubmit" enctype="multipart/form-data">
            <b-form-group label="Libro:">
                <b-form-input style="text-transform:uppercase;" autofocus
                    @keyup="mostrarLibros()" v-model="form.libro"
                    required :disabled="load"
                ></b-form-input>
                <div class="list-group" v-if="resultslibros.length" id="listaL">
                    <a class="list-group-item list-group-item-action" 
                        href="#" @click="datosLibro(libro)"
                        v-for="(libro, i) in resultslibros" v-bind:key="i">
                        {{ libro.titulo }}
                    </a>
                </div>
            </b-form-group>
            <b-form-group label="Tipo">
                <b-form-select v-model="form.tipo" :options="options" required :disabled="load" @change="checkDisponible()"></b-form-select>
            </b-form-group>
            <input :disabled="load" type="file" required id="archivoType"
                class="custom-file" v-on:change="fileChange">
            <label for="archivoType"><i class="fa fa-cloud-upload"></i>  Seleccionar archivo</label> <br>
            <label v-if="form.file"><b>Archivo:</b> {{ form.file ? form.file.name : '' }}</label>
            <b-row>
                <b-col>
                    <b-alert v-if="load" show variant="info">
                        No cierres este recuadro hasta que el archivo termine de cargar
                    </b-alert>
                </b-col>
                <b-col class="text-right" sm="4">
                    <b-button :disabled="load || form.file == null || form.libro_id == null" 
                        class="mt-3" variant="success" type="submit" pill>
                        <i v-if="!load" class="fa fa-plus-circle"></i>
                        <b-spinner v-else type="grow" small></b-spinner>
                        {{ !load ? 'Guardar':'Cargando' }}
                    </b-button>
                </b-col>
            </b-row>
        </form>
    </div>
</template>

<script>
import sweetAlert from '../../../mixins/sweetAlert';
import toast from '../../../mixins/toast';
export default {
    props: ['editorial'],
    mixins: [toast, sweetAlert],
    data(){
        return {
            load: false,
            errorFormat: null,
            form: {
                libro_id: null,
                libro: null,
                file: null,
                tipo: null
            },
            resultslibros: [],
            options: [
                { value: null, text: 'Selecciona una opción', disabled: true },
                { value: 'alumno', text: 'alumno' },
                { value: 'profesor', text: 'profesor' },
                { value: 'demo', text: 'demo' }
            ]
        }
    },
    methods: {
        fileChange(e){
            this.form.file = e.target.files[0];
        },
        onSubmit(e){
            e.preventDefault();

            var fileInput = document.getElementById('archivoType');
            var allowedExtensions = /(\.xlsx)$/i;

            this.load = true;
            if(allowedExtensions.exec(fileInput.value)){
                this.errorFormat = false;
                let formData = new FormData();
                formData.append('libro_id', this.form.libro_id);
                formData.append('file', this.form.file);
                formData.append('tipo', this.form.tipo);
                axios.post('/codes/upload', formData, { headers: { 'content-type': 'multipart/form-data' } })
                .then(response => {
                    this.$emit('addListaLibros', response.data);
                    this.load = false;
                }).catch(error => {
                    this.load = false;
                    this.messageAlert('center', 'warning', 'Ocurrio un problema. erifica que los datos del archivo este correctos e intenta de nuevo.', null, 'info');
                });
            } else {
                this.errorFormat = true;
                this.load = false;
            }
        },
        mostrarLibros(){
            if(this.form.libro.length > 0){
                axios.get('/libro/by_editorial_digital', {params: {titulo: this.form.libro, editorial: this.editorial, type: 'digital'}}).then(response => {
                    this.resultslibros = response.data;
                }).catch(error => {
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                });
            } else{
                this.resultslibros = [];
            }
        },
        datosLibro(libro){
            this.form.libro_id = libro.id;
            this.form.libro = libro.titulo;
            this.form.tipo = null;
            this.resultslibros = [];
        },
        // SI ES LIENCIA/DEMO, REVISAR SI ESTA EN EL INVENTARIO DE CLAVES
        checkDisponible(){
            if(this.form.tipo != 'alumno'){
                this.load = true;
                axios.get('/codes/check_byclave', {params: {libro_id: this.form.libro_id, tipo: this.form.tipo}}).then(response => {
                    if(response.data.piezas == undefined) {
                        this.makeToast('warning', 'Licencia / Demo no existe.');
                        this.form.tipo = null;
                    }
                    this.load = false;
                }).catch(error => {
                    this.load = false;
                });
            }
        },
    }
}
</script>

<style>

</style>