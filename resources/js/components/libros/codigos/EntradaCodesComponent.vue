<template>
    <div>
        <b-row>
            <b-col>
                <b-form-group label="Editorial">
                    <b-form-select v-model="form.editorial" autofocus
                        :disabled="load || form.libros.length > 0" :state="stateE" 
                        :options="options" @change="editorialSelected()">
                    </b-form-select>
                </b-form-group>
            </b-col>
            <b-col v-if="form.editorial == 'MAJESTIC EDUCATION'">
                <b-form-group label="Proveedor">
                    <b-form-select v-model="form.imprenta_id" required
                        :disabled="load || form.libros.length > 0" :options="imprentas">
                    </b-form-select>  
                </b-form-group>
            </b-col>
            <b-col>
                <b-form-group label="Folio">
                    <b-form-input style="text-transform:uppercase;"
                        v-model="form.folio"
                        :disabled="load || !stateE" :state="state"
                        @change="guardar_num()">
                    </b-form-input>
                </b-form-group>
            </b-col>
        </b-row>
        <b-row class="mb-2">
            <b-col sm="8">
                <label>Unidades: <b>{{ form.unidades }}</b></label>
            </b-col>
            <b-col sm="2" class="text-right">
                <b-button variant="primary" block :disabled="!stateE || load" block pill v-b-modal.modal-uploadCodes>
                    <i class="fa fa-cloud-upload"></i> Códigos
                </b-button>
            </b-col>
            <b-col sm="2" class="text-right">
                <b-button :disabled="!stateE || !state || form.unidades <= 0 || load" 
                    @click="subirComprobante()" variant="dark" block pill>
                    <i class="fa fa-arrow-right"></i> Continuar
                </b-button>
            </b-col>
        </b-row>
        <b-table :items="form.libros" :fields="fields">
            <template v-slot:cell(index)="data">
                {{ data.index + 1 }}
            </template>
            <template #cell(codes)="row">
                <b-button size="sm" @click="row.toggleDetails" pill variant="info">
                {{ row.detailsShowing ? 'Ocultar' : 'Mostrar'}}
                </b-button>
            </template>
            <template #row-details="row">
                <b-row>
                    <b-col sm="3"></b-col>
                    <b-col sm="6">
                        <b-table :items="row.item.codes" :fields="fieldsCodes">
                            <template v-slot:cell(index)="data">
                                {{ data.index + 1 }}
                            </template>
                        </b-table>
                    </b-col>
                    <b-col sm="3"></b-col>
                </b-row>
            </template>
        </b-table>
        <!-- MODAL PARA CARGAR CODIGOS -->
        <b-modal id="modal-uploadCodes" title="Cargar códigos" hide-footer>
            <upload-codes-component :editorial="form.editorial" @addListaLibros="addListaLibros"></upload-codes-component>
        </b-modal>
        <!-- MODAL PARA SUBIR COMPROBANTE -->
        <b-modal id="modal-uploadComprobante" title="Subir comprobante y Guardar" hide-footer>
            <form @submit="save_entrada" enctype="multipart/form-data">
                <subir-foto-component :disabled="load || !stateE" :allowExt="allowExt"
                    :titulo="'Subir factura'" @uploadImage="uploadImage"></subir-foto-component>
                <b-button :disabled="load" type="submit" variant="success" block pill>
                    <i class="fa fa-check-circle"></i> Guardar
                </b-button>
            </form>
        </b-modal>
    </div>
</template>

<script>
import toast from '../../../mixins/toast';
import getEditoriales from '../../../mixins/getEditoriales';
import SubirFotoComponent from '../../funciones/SubirFotoComponent.vue';
import sweetAlert from '../../../mixins/sweetAlert';
import getImprentas from '../../../mixins/editoriales/getImprentas';
export default {
    mixins: [toast, getEditoriales, sweetAlert, getImprentas],
    components: { SubirFotoComponent },
    data(){
        return {
            form: {
                editorial: null,
                imprenta_id: null,
                folio: null,
                unidades: 0,
                libros: [],
                file: null
            },
            state: null,
            stateE: null,
            fields: [
                {key:'index', label:'N.'},
                'libro', 'unidades', 'tipo',
                {key:'codes', label:'Códigos'},
            ],
            fieldsCodes: [
                {key:'index', label:'N.'},
                {key:'codigo', label:'Código'},
            ],
            allowExt: /(\.jpg|\.jpeg|\.png|\.pdf)$/i
        }
    },
    created: function(){
        this.get_editoriales();
    },
    methods: {
        // MOSTRAR IMPRENTAS EN CASO DE SELECCIONAR MAJESTIC COMO EDITORIAL
        editorialSelected(){
            if(this.form.editorial == 'MAJESTIC EDUCATION'){
                this.getImprentas('digital');
            }
            this.stateE = true;
        },
        // VALIDAR EL FOLIO
        guardar_num(){
            if(this.form.folio.length > 0){
                axios.get('/buscarFolio', {params: {folio: this.form.folio}}).then(response => {
                    if(response.data.id != undefined){
                        this.state = false;
                        this.makeToast('warning', 'El folio ya existe.');
                    }
                    else{
                        this.state = true;
                    }
                }).catch(error => {
                    this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
                });
            }
            else{
                this.state = false;
                this.makeToast('warning', 'Definir folio.');
            }
        },
        // MOSTRAR EN LA TABLA EL LIBRO CON CODIGOS
        addListaLibros(libro){
            if(libro.unidades > 0){
                this.form.libros.push(libro);
                this.makeToast('success', 'Los códigos se subieron correctamente.');
            } else {
                this.makeToast('warning', 'Los códigos ya han sido guardados o los datos no coinciden con el libro seleccionado.');
            }
            this.form.unidades += libro.unidades;
            this.$bvModal.hide('modal-uploadCodes')
        },
        // GUARDAR ENTRADA
        save_entrada(e){
            e.preventDefault();
            this.load = true;
            let formData = new FormData();
            formData.append('file', this.form.file, this.form.file.name);
            formData.append('unidades', this.form.unidades);
            formData.append('folio', this.form.folio);
            formData.append('editorial', this.form.editorial);
            formData.append('imprenta_id', this.form.imprenta_id);
            formData.append('libros', JSON.stringify(this.form.libros));
            axios.post('/entradas/store_codes', formData, { 
                headers: { 'content-type': 'multipart/form-data' } }).then(response => {
                    this.messageAlert('center', 'success', 'La entrada se creo correctamente', null, 'reload');
                    this.load = false;
            }).catch(error => {
                this.load = false;
                this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
            });
        },
        subirComprobante(){
            this.form.file = null;
            this.$bvModal.show('modal-uploadComprobante');
        },
        uploadImage(file){
            this.form.file = file;
        }
    }
}
</script>

<style>

</style>