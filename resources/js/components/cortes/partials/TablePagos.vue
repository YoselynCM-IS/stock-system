<template>
    <div>
        <b-table v-if="remdepositos.length > 0"
            :items="remdepositos" :fields="fieldsPagos" responsive>
            <template v-slot:cell(created_at)="row">
                {{ row.item.created_at | momentDet }}
            </template>
            <template v-slot:cell(pago)="row">
                ${{ row.item.pago | formatNumber }}
            </template>
            <template v-if="showTitle" #thead-top="row">
                <tr><th colspan="5" class="text-center">Pagos</th></tr>
            </template>
            <template v-if="role_id == 1 || role_id == 2 || role_id == 6" 
                v-slot:cell(fotos)="row">
                <b-button v-if="!row.item.foto" pill size="sm" variant="info" 
                    @click="selectImage(row.item)">
                    <i class="fa fa-upload"></i>
                </b-button>
                <b-button v-else pill size="sm" variant="dark" 
                    @click="showImage(row.item)">
                    <i class="fa fa-eye"></i>
                </b-button>
                <!-- <a v-else :href="row.item.foto.public_url" target="_blank">
                    Ver foto
                </a> -->
            </template>
            <template v-if="role_id == 6" v-slot:cell(actions)="row">
                <b-button pill size="sm" variant="primary" @click="movePago(row.item)">
                    <i class="fa fa-exchange"></i>
                </b-button>
                <b-button pill size="sm" variant="warning" @click="editPago(row.item)">
                    <i class="fa fa-pencil"></i>
                </b-button>
                <b-button pill size="sm" variant="danger" @click="deletePago(row.item)">
                    <i class="fa fa-close"></i>
                </b-button>
            </template>
        </b-table>
        <alert-v-component v-else :text="'pagos'"></alert-v-component>

        <!-- MODALAS -->
        <!-- Mover pago de corte -->
        <b-modal ref="show-move-pago" hide-footer size="sm" title="Mover pago">
            <select-corte-pagos-component :form="form" :options="options" :move="true"
                :cortes="cortes" @pagosGuardados="pagosGuardados"></select-corte-pagos-component>
        </b-modal>
        <!-- Editar pago -->
        <b-modal ref="show-editar-pago" hide-footer size="sm" title="Editar pago">
            <edit-pago-component :form="formPago" @updPayment="updPayment"></edit-pago-component>
        </b-modal>
        <!-- Eliminar pago -->
        <b-modal ref="show-eliminar-pago" hide-footer size="sm" title="Eliminar pago">
            <div class="text-center">
                <p>¿Estas seguro de eliminar el pago?</p>
                <b-button pill variant="danger" @click="confirmDelete()">
                    <i class="fa fa-close"></i> Confirmar
                </b-button>
            </div>
        </b-modal>
        <!-- SUBIR PAGO -->
        <b-modal ref="show-upload-pago" hide-footer size="sm" title="Subir foto del pago">
            <form @submit="saveImage" enctype="multipart/form-data">
                <subir-foto-component :disabled="load" :titulo="'Subir pago'" :allowExt="allowExt" @uploadImage="uploadImage"></subir-foto-component>
                <div class="text-right mt-3">
                    <b-button pill :disabled="load" variant="success" type="submit">
                        <i class="fa fa-plus-circle"> Subir</i> 
                    </b-button>
                </div>
            </form>
        </b-modal>
        <!-- MOSTRAR PAGO -->
        <b-modal ref="show-pago" hide-footer size="lg" title="Comprobante de pago">
            <img :src="image_url" alt="Comprobante de pago" width="100%">
        </b-modal>
    </div>
</template>

<script>
import SubirFotoComponent from '../../funciones/SubirFotoComponent.vue';
import formatNumber from '../../../mixins/formatNumber';
import AlertVComponent from './AlertVComponent.vue';
import setCortes from '../../../mixins/setCortes';
import toast from '../../../mixins/toast';
import EditPagoComponent from './EditPagoComponent.vue';
import sweetAlert from '../../../mixins/sweetAlert';
import moment from '../../../mixins/moment';
export default {
    components: {AlertVComponent, EditPagoComponent, SubirFotoComponent},
    props: ['remdepositos', 'cortePagar', 'showTitle', 'cliente_id','role_id'],
    mixins: [formatNumber, setCortes, toast, sweetAlert, moment],
    data(){
        return {
            fieldsPagos: [
                { key: 'created_at', label: 'Fecha de registro' },
                { key: 'fecha', label: 'Fecha del pago' },
                { key: 'ingresado_por', label: 'Ingresado por' },
                { key: 'pago', label: 'Pago' },
                { key: 'nota', label: 'Concepto' },
                { key: 'fotos', label: '' },
                { key: 'actions', label: '' }
            ],
            form: {
                pago_id: null,
                corte_id: null,
                corte_id_favor: null,
                cliente_id: this.cliente_id,
                total_selected: 0,
            },
            formPago: {
                id: null,
                pago: null,
                fecha: null,
                nota: null,
                corte_pagar: this.cortePagar
            },
            pago_id: null,
            options: [],
            load: false,
            cortes: [],
            formImage: {
                pagoid: null, file: null
            },
            // file: null,
            errors: {},
            allowExt: /(\.jpg|\.jpeg|\.png)$/i,
            image_url: null
        }
    },
    methods: {
        // MOVER PAGO
        movePago(pago){
            this.load = true;
            axios.get('/cortes/get_all').then(response => {
                this.form.pago_id = pago.id;
                this.form.corte_id = null;
                this.form.total_selected = pago.pago;
                this.cortes = response.data;
                this.options = this.setCortes(response.data, null);
                this.$refs['show-move-pago'].show();
                this.load = false;
            }).catch(error => {
                this.load = false;
            });
        },
        // PAGO MOVIDO
        pagosGuardados(respuesta){
            this.$refs['show-move-pago'].hide();
            if(respuesta) {
                this.messageAlert('center', 'success', 'El pago se movio correctamente.', null, 'reload');
            } else {
                this.makeToast('warning', 'Vuelve a elegir el pago.');
            }
        },
        // EDITAR PAGO
        editPago(pago) {
            this.formPago.id = pago.id;
            this.formPago.pago = pago.pago;
            this.formPago.fecha = pago.fecha;
            this.formPago.nota = pago.nota;
            this.$refs['show-editar-pago'].show();
        },
        // PAGO ACTUALIZADO
        updPayment(pago){
            this.$refs['show-editar-pago'].hide();
            this.messageAlert('center', 'success', 'El pago se actualizo correctamente.', null, 'reload');
        },
        // ELIMINAR PAGO
        deletePago(pago){
            this.pago_id = pago.id;
            this.$refs['show-eliminar-pago'].show();
        },
        // CONFIRMAR PARA BORRAR EL PAGO
        confirmDelete(){
            this.load = true;
            axios.delete('/cortes/delete_payment', {params: {pago_id: this.pago_id}}).then(response => {
                this.$refs['show-eliminar-pago'].hide();
                this.messageAlert('center', 'success', 'El pago se elimino correctamente.', null, 'reload');
                this.load = false;
            }).catch(error => {
                this.load = false;
                this.makeToast('danger', 'Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.');
            }); 
        },
        selectImage(pago){
            this.formImage.pagoid = pago.id;
            this.$refs['show-upload-pago'].show();
        },
        fileChange(e){
            var fileInput = document.getElementById('archivoType');
            var allowedExtensions = /(\.jpg|\.jpeg|\.png)$/i;
            
            if(allowedExtensions.exec(fileInput.value)){
                this.formImage.file = e.target.files[0];
            } else {
                this.messageAlert('center', 'warning', 'Formato de imagen no permitido, solo puede ser en formato imagen (jpg, jpeg, png)', null, 'info');
            }
        },
        saveImage(e){
            e.preventDefault();
            this.load = true;
            let formData = new FormData();
            formData.append('file', this.formImage.file, this.formImage.file.name);
            formData.append('pagoid', this.formImage.pagoid);
            axios.post('/cortes/upload_payment', formData, { 
                headers: { 'content-type': 'multipart/form-data' } }).then(response => {
                this.messageAlert('center', 'success', 'La comprobante se guardo correctamente.', null, 'reload');
                this.load = false;
            }).catch(error => {
                this.load = false;
            });
        },
        uploadImage(file){
            this.formImage.file = file;
        },
        showImage(pago) {
            this.load = true;
            this.image_url = null;
            axios.get('/cortes/image_url', {params: {pago_id: pago.id}}).then(response => {
                this.image_url = response.data;
                this.$refs['show-pago'].show();
                this.load = false;
            }).catch(error => {
                this.load = false;
            });
        }
    }
}
</script>

<style>
    input[type="file"]#archivoType {
        width: 0.1px;
        height: 0.1px;
        opacity: 0;
        overflow: hidden;
        position: absolute;
        z-index: -1;
    }
   
    label[for="archivoType"] {
        font-size: 14px;
        font-weight: 600;
        color: #fff;
        background-color: #106BA0;
        display: inline-block;
        transition: all .5s;
        cursor: pointer;
        padding: 15px 40px !important;
        text-transform: uppercase;
        width: fit-content;
        text-align: center;
        border-radius: 20px;
    }
</style>