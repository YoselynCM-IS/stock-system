<template>
    <div>
        <b-form @submit.prevent="onSubmit()">
            <b-row class="my-1">
                <label align="right" class="col-md-3">Serie</label>
                <div class="col-md-9">
                    <b-form-input style="text-transform:uppercase;" @keyup="getSeries()"
                        v-model="form.serie.serie" :disabled="loaded" required autofocus>
                    </b-form-input>
                    <div class="list-group" v-if="resultsSeries.length" id="listaL">
                        <a class="list-group-item list-group-item-action" href="#"
                            v-for="(serie, i) in resultsSeries" v-bind:key="i" @click="datosSerie(serie)">
                            {{ serie.serie }}
                        </a>
                    </div>
                    <div v-if="errors && errors['serie.id']" class="text-danger">{{ errors['serie.id'][0] }}</div>
                    <div v-if="errors && errors['serie.serie']" class="text-danger">{{ errors['serie.serie'][0] }}</div>
                </div>
            </b-row>
            <b-row class="my-1">
                <label align="right" class="col-md-3">Tipo</label>
                <div class="col-md-9">
                    <b-form-select v-model="form.type" :disabled="loaded" :options="types" required></b-form-select>
                    <div v-if="errors && errors.type" class="text-danger">{{ errors.type[0] }}</div>
                </div>
            </b-row>
            <b-row class="my-1">
                <label align="right" class="col-md-3">Titulo</label>
                <div class="col-md-9">
                    <b-form-input
                        style="text-transform:uppercase;"
                        v-model="form.titulo"
                        :disabled="loaded"
                        required>
                    </b-form-input>
                    <div v-if="errors && errors.titulo" class="text-danger">{{ errors.titulo[0] }}</div>
                </div>
            </b-row>
            <b-row class="my-1">
                <label align="right" class="col-md-3">ISBN</label>
                <div class="col-md-9">
                    <b-form-input 
                        v-model="form.ISBN" 
                        :disabled="loaded"
                        required>
                    </b-form-input>
                    <div v-if="errors && errors.ISBN" class="text-danger">{{ errors.ISBN[0] }}</div>
                </div>
            </b-row>
            <b-row class="my-1">
                <label align="right" class="col-md-3">Autor</label>
                <div class="col-md-9">
                    <b-form-input 
                        style="text-transform:uppercase;"
                        :disabled="loaded"
                        v-model="form.autor">
                    </b-form-input>
                    <div v-if="errors && errors.autor" class="text-danger">{{ errors.autor[0] }}</div>
                </div>
            </b-row>
            <b-row class="my-1">
                <label align="right" class="col-md-3">Editorial</label>
                <div class="col-md-9">
                    <b-form-select v-model="form.editorial" :disabled="loaded" :options="options" required></b-form-select>
                    <div v-if="errors && errors.editorial" class="text-danger">{{ errors.editorial[0] }}</div>
                </div>
            </b-row>
            <hr>
            <div class="text-right">
                <b-button type="submit" :disabled="loaded" variant="success">
                    <i class="fa fa-check"></i> {{ !loaded ? 'Guardar' : 'Guardando' }} <b-spinner small v-if="loaded"></b-spinner>
                </b-button>
            </div>
        </b-form>
    </div>
</template>

<script>
    export default {
        props: ['form', 'addEdit', 'listEditoriales'],
        data() {
            return {
                errors: {},
                success: false,
                loaded: false,
                options: this.listEditoriales,
                types: [
                    { value: null, text: 'Seleccionar opción', disabled: true },
                    { value: 'venta', text: 'Venta' },
                    { value: 'digital', text: 'Digital' },
                    { value: 'promocion', text: 'Promoción' }
                ],
                //DATOS PARA AGREGAR/BUSCAR LA SERIE
                resultsSeries: [] 
            }
        },
        methods: {
            // BUSCAR SERIE
            getSeries(){
                this.form.serie.id = null;
                if(this.form.serie.serie !==  null && this.form.serie.serie.length > 0){
                    axios.get('/libro/serie/get_series', {params: { querySerie: this.form.serie.serie}}).then(response => {
                        if(response.data.length == 0 && this.addEdit) this.resultsSeries = [{id: 0, serie: 'NUEVA SERIE'}];
                        else this.resultsSeries = response.data;
                    }).catch(error => { });
                } else {
                    this.resultsSeries = [];
                }
            },
            // SELECCIONAR LA SERIE
            datosSerie(serie){
                this.resultsSeries = [];
                this.form.serie.id = serie.id;
                if(this.form.serie.id > 0) this.form.serie.serie = serie.serie;
            },
            // GUARDAR UN NUEVO LIBRO
            onSubmit() {
                this.loaded = true;
                this.success = false;
                this.errors = {};

                let a = null;
                if(this.addEdit)  a = axios.post('/libro/store', this.form);
                else a = axios.put('/libro/update', this.form);
                
                a.then(response => {
                    this.loaded = false;
                    this.success = true;
                    this.$emit('actualizarLista', response.data);
                }).catch(error => {
                    this.errors = {};
                    this.loaded = false;
                    if (error.response.status === 422) {
                        this.errors = error.response.data.errors || {};
                    } else{
                        this.$bvToast.toast('Ocurrió un problema. Verifica tu conexión a internet y/o vuelve a intentar.', {
                            title: 'Mensaje',
                            variant: 'danger',
                            solid: true
                        });
                    }
                });
            }
        }
    }
</script>
