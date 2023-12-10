<template>
    <div>
        <b-form @submit.prevent="actualizarLibro()">
            <b-row class="my-1">
                <label align="right" class="col-md-3">Tipo</label>
                <div class="col-md-9">
                    <b-form-select v-model="libro.type" :disabled="loaded" :options="types" required></b-form-select>
                    <div v-if="errors && errors.type" class="text-danger">{{ errors.type[0] }}</div>
                </div>
            </b-row>
            <b-row class="my-1">
                <label align="right" class="col-md-3">Titulo</label>
                <div class="col-md-9">
                    <b-form-input 
                        style="text-transform:uppercase;"
                        v-model="libro.titulo"
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
                        v-model="libro.ISBN" 
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
                        v-model="libro.autor">
                    </b-form-input>
                    <div v-if="errors && errors.autor" class="text-danger">{{ errors.autor[0] }}</div>
                </div>
            </b-row>
            <b-row class="my-1">
                <label align="right" class="col-md-3">Editorial</label>
                <div class="col-md-9">
                    <b-form-select v-model="libro.editorial" :disabled="loaded" :options="options" required></b-form-select>
                    <div v-if="errors && errors.editorial" class="text-danger">{{ errors.editorial[0] }}</div>
                </div>
            </b-row>
            <div class="text-right">
                <b-button type="submit" :disabled="loaded" variant="success">
                    <i class="fa fa-check"></i> {{ !loaded ? 'Actualizar' : 'Actualizando' }} <b-spinner small v-if="loaded"></b-spinner>
                </b-button>
            </div>
        </b-form>
        <b-alert v-if="success" show dismissible>
            <i class="fa fa-check"></i>Libro actualizado
        </b-alert>
    </div>
</template>

<script>
    export default {
        props: ['formlibro', 'listEditoriales'],
        data() {
            return {
                errors: {},
                libro: {
                    id: this.formlibro.id,
                    type: this.formlibro.type,
                    titulo: this.formlibro.titulo,
                    ISBN: this.formlibro.ISBN,
                    autor: this.formlibro.autor,
                    editorial: this.formlibro.editorial
                },
                options: this.listEditoriales,
                posicion: 0,
                loaded: false,
                success: false,
                types: [
                    { value: null, text: 'Seleccionar opción', disabled: true },
                    { value: 'venta', text: 'Venta' },
                    { value: 'digital', text: 'Digital' },
                    { value: 'promocion', text: 'Promoción' }
                ]
            }
        },
        methods: {
            // ACTUALIZAR DATOS DEL LIBRO
            actualizarLibro(){
                this.loaded = true;
                axios.put('/actualizar_libro', this.libro).then(response => {
                    this.errors = {};
                    this.loaded = false;
                    this.$emit('actualizarLibro', response.data);
                })
                .catch(error => {
                    this.errors = {};
                    this.loaded = false;
                    if (error.response.status === 422) {
                        this.errors = error.response.data.errors || {};
                    }
                    else{
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