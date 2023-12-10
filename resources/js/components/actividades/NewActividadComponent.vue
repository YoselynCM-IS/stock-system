<template>
    <div>
        <b-form @submit.prevent="onSubmit()">
            <b-row class="mb-3">
                <b-col></b-col>
                <b-col sm="3">
                    <b-button type="submit" :disabled="loaded" variant="success"
                        pill block>
                        <i class="fa fa-check"></i> {{ !loaded ? 'Guardar' : 'Guardando' }} <b-spinner small v-if="loaded"></b-spinner>
                    </b-button>
                </b-col>
            </b-row>
            <div>
                <b-row class="mb-2">
                    <b-col sm="3" class="text-right"><label>Nombre de la actividad</label></b-col>
                    <b-col>
                        <b-form-input v-model="form.nombre" required :disabled="loaded" autofocus
                                style="text-transform:uppercase;"></b-form-input>
                    </b-col>
                </b-row>
                <b-row class="mb-2">
                    <b-col sm="3" class="text-right"><label>Tipo</label></b-col>
                    <b-col>
                        <b-form-select v-model="form.tipo" :options="tipos" required
                            :disabled="loaded" @change="initializeValues()"></b-form-select>
                    </b-col>
                </b-row>
                <b-row class="mb-2" v-if="form.tipo == 'reunion' || form.tipo == 'videoconferencia'">
                    <b-col sm="3" class="text-right"><label>{{setTitulo(form.tipo)}}</label></b-col>
                    <b-col>
                        <b-form-textarea v-model="form.lugar" required :disabled="loaded" rows="3" max-rows="6"></b-form-textarea>
                    </b-col>
                </b-row>
                <b-row class="mb-2">
                    <b-col sm="3" class="text-right"><label>Fecha</label></b-col>
                    <b-col>
                        <b-form-datepicker v-model="form.fecha" :disabled="loaded" required></b-form-datepicker>
                    </b-col>
                    <b-col v-if="form.tipo != 'nota'" sm="1" class="text-right"><label>Hora</label></b-col>
                    <b-col v-if="form.tipo != 'nota'">
                        <b-form-timepicker v-model="form.hora" locale="en" :disabled="loaded" required></b-form-timepicker>
                    </b-col>
                </b-row>
                <b-row class="mb-2">
                    <b-col sm="3" class="text-right"><label>Descripción</label></b-col>
                    <b-col>
                        <b-form-textarea v-model="form.descripcion" rows="3" max-rows="6"
                            :disabled="loaded" required></b-form-textarea>
                    </b-col>
                </b-row>
                <b-row class="mb-2">
                    <b-col sm="3" class="text-right"><label>Recordar</label></b-col>
                    <b-col>
                        <b-form-select v-model="form.recordatorio" :options="horas" required
                            :disabled="loaded"></b-form-select>
                    </b-col>
                </b-row>
                <b-row class="mb-2">
                    <b-col sm="3" class="text-right"><label>Relacionar cliente(s)</label></b-col>
                    <b-col>
                        <b-input v-model="queryCliente" @keyup="mostrarClientes()"
                            style="text-transform:uppercase;" :disabled="loaded">
                        </b-input>
                        <div class="list-group" v-if="clientes.length" id="listP">
                            <a href="#" v-bind:key="i" class="list-group-item list-group-item-action" 
                                v-for="(cliente, i) in clientes" @click="selectCliente(cliente)">
                                {{ cliente.name }}
                            </a>
                        </div>
                    </b-col>
                    <b-col sm="2">
                        <b-button variant="dark" pill block :disabled="loaded || !selCliente.id"
                            @click="addCliente()">
                            <i class="fa fa-plus-circle"></i> Agregar
                        </b-button>
                    </b-col>
                </b-row>
                <div v-if="form.clientes.length > 0">
                    <list-clientes :clientes="form.clientes" :quitar="true"></list-clientes>
                </div>
            </div>
        </b-form>
    </div>
</template>

<script>
import searchCliente from '../../mixins/searchCliente';
import setTitulo from '../../mixins/setTitulo';
import ListClientes from './partials/ListClientes.vue';
import toast from '../../mixins/toast';
export default {
    mixins: [searchCliente, setTitulo, toast],
    components: {ListClientes},
    data(){
        return {
            loaded: false, 
            form: {
                tipo: null,
                lugar: null,
                fecha: null,
                hora: null,
                descripcion: null,
                clientes: [],
                recordatorio: null
            },
            errors: {},
            tipos: [
                { value: null, text: 'Selecciona una opción', disabled: true},
                { value: 'reunion', text: 'Reunión' },
                { value: 'videoconferencia', text: 'Video conferencia' },
                { value: 'llamar', text: 'Llamar' },
                { value: 'enviarcorreo', text: 'Enviar correo' },
                { value: 'nota', text: 'Nota' }
            ],
            horas: [
                { value: null, text: 'Selecciona una opción', disabled: true},
                { value: '15', text: '15 minutos antes' },
                { value: '30', text: '30 minutos antes' },
                { value: '60', text: '1 hora antes' },
                { value: '120', text: '2 horas antes' }
            ],
            selCliente: {}
        }
    },
    created: function(){
        
    },
    methods: {
        initializeValues(){
            this.form.lugar = null;
            if(this.form.tipo == 'nota') this.form.hora = null;
        },
        selectCliente(cliente){
            this.selCliente = cliente;
            this.queryCliente = cliente.name;
            this.clientes = [];
        },
        addCliente(){
            this.form.clientes.push(this.selCliente);
            this.selCliente = {};
            this.queryCliente = null;
        },
        onSubmit(){
            if(this.form.clientes.length > 0){
                this.loaded = true;
                axios.post('/actividades/store', this.form).then(response => {
                    this.$emit('actividadSaved', response.data);
                    this.loaded = false;
                }).catch(error => {
                    this.loaded = false;
                    if (error.response.status === 422) {
                        this.errors = error.response.data.errors || {};
                    }
                });
            } else {
                this.makeToast('warning', 'Es necesario agregar mínimo un cliente');
            }
        }
    }
}
</script>

<style>

</style>