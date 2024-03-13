<template>
    <div>
        <b-form @submit="saveEnvio" enctype="multipart/form-data">
            <b-row>
                <b-col>
                    <div v-if="envio == null">
                        <b-row>
                            <b-col>
                                <p><b>¿La {{ tipo }} se envió mediante paquetería?</b></p>
                            </b-col>
                            <b-col sm="2">
                                <b-button size="sm" @click="envio = true" pill block variant="dark">SI</b-button>
                            </b-col>
                            <b-col sm="2">
                                <b-button size="sm" @click="envio = false" pill block variant="dark">NO</b-button>
                            </b-col>
                        </b-row>
                    </div>
                </b-col>
            </b-row>
            <b-row v-if="envio">
                <b-col>
                    <div v-if="destinatario == null" class="mb-2">
                        <p><b>¿Agregar o buscar destinatario?</b></p>
                        <b-row>
                            <b-col sm="3">
                                <b-button size="sm" @click="destinatario = true" pill block variant="dark">Agregar</b-button>
                            </b-col>
                            <b-col sm="3">
                                <b-button size="sm" @click="destinatario = false" pill block variant="info">Buscar</b-button>
                            </b-col>
                        </b-row>
                    </div>
                    <div v-if="destinatario == false">
                        <b-form-group label="Buscar destinatario">
                            <b-input v-model="queryDestinatario"
                                style="text-transform:uppercase;"
                                @keyup="mostrarCDests()"></b-input>
                            <div class="list-group" v-if="destinatarios.length" id="listP">
                                <a href="#" v-bind:key="i" class="list-group-item list-group-item-action" 
                                    v-for="(destinatario, i) in destinatarios" @click="selectDest(destinatario)">
                                    {{ destinatario.destinatario }}
                                </a>
                            </div>
                        </b-form-group>
                    </div>
                    <div v-if="destinatario || form.destinatario.id > 0">
                        <b-form-group label="Destinatario">
                            <b-form-input v-model="form.destinatario.destinatario" :disabled="load || form.destinatario.id > 0" 
                                required style="text-transform:uppercase;"></b-form-input>
                        </b-form-group>
                        <b-form-group label="Dirección">
                            <b-form-textarea v-model="form.destinatario.direccion" rows="2" max-rows="6"
                                :disabled="load || form.destinatario.id > 0" required style="text-transform:uppercase;"></b-form-textarea>
                        </b-form-group>
                        <b-form-group label="RFC">
                            <b-form-input v-model="form.destinatario.rfc" :disabled="load || form.destinatario.id > 0" 
                                required style="text-transform:uppercase;"></b-form-input>
                        </b-form-group>
                        <b-form-group label="Teléfono">
                            <b-form-input v-model="form.destinatario.telefono" :disabled="load || form.destinatario.id > 0" required></b-form-input>
                        </b-form-group>
                        <b-form-group label="Régimen fiscal">
                            <b-form-select :disabled="load || form.destinatario.id > 0" v-model="form.destinatario.regimen_fiscal" :options="regimenes" required></b-form-select>
                        </b-form-group>
                    </div>
                </b-col>
                <b-col>
                    <div v-if="destinatario !== null">
                        <b-form-group label="Nombre de la paquetería">
                            <b-form-input v-model="form.paqueteria.paqueteria" :disabled="load" 
                                required style="text-transform:uppercase;">
                        </b-form-input>
                        </b-form-group>
                        <b-form-group label="Tipo de envió">
                            <b-form-select :disabled="load" v-model="form.paqueteria.tipo_envio" :options="tipos" required></b-form-select>
                        </b-form-group>
                        <b-form-group label="Fecha de envió">
                            <b-form-datepicker v-model="form.paqueteria.fecha_envio" :disabled="load" required></b-form-datepicker>
                        </b-form-group>
                        <b-form-group label="Costo de envió">
                            <b-form-input v-model="form.paqueteria.precio" type="number" :disabled="load" required></b-form-input>
                        </b-form-group>
                        <b-form-group label="Número de guía">
                            <b-form-textarea v-model="form.paqueteria.guia" :disabled="load" required rows="3" max-rows="6"></b-form-textarea>
                        </b-form-group>
                        <subir-foto-component :disabled="load" :allowExt="allowExt"
                            :titulo="'Subir guía'" @uploadImage="uploadImage"></subir-foto-component>
                    </div>
                </b-col>
            </b-row>
            <b-alert v-if="envio == false" show variant="success">
                <i class="fa fa-info-circle"></i> Presiona en <b>Guardar</b> para guardar la opción seleccionada.
            </b-alert>
            <b-row class="mt-3">
                <b-col>
                    <b-alert show variant="info">
                        <i class="fa fa-exclamation-circle"></i> Verificar los datos antes de presionar <b>Guardar</b>, ya que después no se podrán realizar cambios.
                    </b-alert>
                </b-col>
                <b-col sm="3">
                    <b-button type="submit" :disabled="load || envio == null || (envio && form.paqueteria.file == null)" variant="success" block pill>
                        <i class="fa fa-check"></i> Guardar <b-spinner v-if="load" small></b-spinner>
                    </b-button>
                </b-col>
            </b-row>
        </b-form>
    </div>
</template>

<script>
import SubirFotoComponent from '../SubirFotoComponent.vue';
export default {
    components: { SubirFotoComponent },
    props: ['enlace_id', 'ruta', 'tipo'],
    data(){
        return {
            options: [],
            load: false,
            envio: null,
            destinatario: null,
            form: {
                enlace_id: null,
                paqueteria: {
                    paqueteria: null,
                    tipo_envio: null,
                    precio: 0,
                    fecha_envio: null,
                    guia: null,
                    file: null,
                },
                destinatario: {
                    id: null,
                    destinatario: null,
                    rfc: null,
                    telefono: null,
                    direccion: null,
                    regimen_fiscal: null
                }
            },
            regimenes: [
                {value: null, text: 'Selecciona una opción', disabled: true},
                {value: 'pf con ae', text: 'Persona física sin actividad empresarial'},
                {value: 'pf sin ae', text: 'Persona física con actividad empresarial'},
                {value: 'pm de lg', text: 'Ley general de personas morales'},
                {value: 'pm sin fl', text: 'Personas morales sin fines lucrativos'}
            ],
            tipos: [
                {value: null, text: 'Selecciona una opción', disabled: true},
                {value: 'terrestre', text: 'Terrestre'},
                {value: 'aereo', text: 'Aéreo'}
            ],
            queryDestinatario: null,
            destinatarios: [],
            allowExt: /(\.jpg|\.jpeg|\.png|\.pdf)$/i
        }
    },
    methods: {
        // GUARDAR INFORMACION DE PAQUETERIA
        saveEnvio(e) {
            e.preventDefault();
            this.load = true;
            this.form.enlace_id = this.enlace_id;
            axios.post(this.ruta, this.set_append()).then(response => {
                this.$emit('savedEnvio', response.data);
                this.load = false;
            }).catch(error => {
                this.load = false;
            });
        },
        set_append() {
            let formData = new FormData();
            formData.append('envio', this.envio);
            formData.append('enlace_id', this.form.enlace_id);
            formData.append('p_paqueteria', this.form.paqueteria.paqueteria);
            formData.append('p_tipo_envio', this.form.paqueteria.tipo_envio);
            formData.append('p_precio', this.form.paqueteria.precio);
            formData.append('p_fecha_envio', this.form.paqueteria.fecha_envio);
            formData.append('p_guia', this.form.paqueteria.guia);
            formData.append('p_file', this.form.paqueteria.file);
            formData.append('d_id', this.form.destinatario.id);
            formData.append('d_destinatario', this.form.destinatario.destinatario);
            formData.append('d_rfc', this.form.destinatario.rfc);
            formData.append('d_telefono', this.form.destinatario.telefono);
            formData.append('d_direccion', this.form.destinatario.direccion);
            formData.append('d_regimen_fiscal', this.form.destinatario.regimen_fiscal);

            return formData;
        },
        mostrarCDests(){
            if(this.queryDestinatario.length > 0){
                axios.get('/clientes/get_destinatarios', {params: {queryDestinatario: this.queryDestinatario}}).then(response => {
                    this.destinatarios = response.data;
                }); 
            } else {
                this.destinatarios = [];
            }
        },
        selectDest(destinatario){
            this.form.destinatario.id = destinatario.id;
            this.form.destinatario.destinatario = destinatario.destinatario;
            this.form.destinatario.rfc = destinatario.rfc;
            this.form.destinatario.telefono = destinatario.telefono;
            this.form.destinatario.direccion = destinatario.direccion;
            this.form.destinatario.regimen_fiscal = destinatario.regimen_fiscal;
            this.queryDestinatario = destinatario.destinatario;
            this.destinatarios = [];
        },
        uploadImage(file) {
            this.form.paqueteria.file = file;
        }
    }
}
</script>

<style>

</style>