<template>
    <div>
        <b-form-group>
            <input :disabled="disabled" type="file" id="archivoType" 
                v-on:change="fileChange" required name="file">
            <label for="archivoType">
                <i class="fa fa-upload"></i> {{titulo}}
            </label>
            <p v-if="file.name">
                ARCHIVO: <b>{{ file.name }}</b>
            </p>
            <!-- <div v-if="errors && errors.file" class="text-danger">
                La foto debe tener un tamaño máximo de 3MB y solo formato jpg, png, jpeg
            </div> -->
        </b-form-group>
    </div>
</template>

<script>
import sweetAlert from '../../mixins/sweetAlert';
export default {
    props: ['disabled', 'titulo', 'allowExt'],
    mixins: [sweetAlert],
    data(){
        return {
            file: {}
        }
    },
    methods: {
        fileChange(e){
            var fileInput = document.getElementById('archivoType');
            // var allowExt = /(\.jpg|\.jpeg|\.png|\.pdf)$/i;
            
            if(this.allowExt.exec(fileInput.value)){
                this.file = e.target.files[0];
                this.$emit('uploadImage', this.file);
            } else {
                this.messageAlert('center', 'warning', 'Revisar formato de archivo. Formato de archivo no permitido', null, 'info');
            }
        },
    }
}
</script>

<style>

</style>