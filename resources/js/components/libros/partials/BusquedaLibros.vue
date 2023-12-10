<template>
    <div>
        <b-form-group :label="text">
            <b-form-input style="text-transform:uppercase;" required
                @keyup="searchLibros()" v-model="queryLibro" 
            ></b-form-input>
            <div class="list-group" v-if="results.length" id="listaL">
                <a class="list-group-item list-group-item-action" 
                    href="#" @click="libroSelect(libro)"
                    v-for="(libro, i) in results" v-bind:key="i">
                    {{ libro.titulo }}
                </a>
            </div>
        </b-form-group>
    </div>
</template>

<script>
export default {
    props: ['text', 'type', 'results'],
    data() {
        return {
            queryLibro: null
        }
    },
    methods: {
        // BUSQUEDA DE LIBROS
        searchLibros() {
            this.$emit('searchLibros', this.queryLibro, this.type);
        },
        libroSelect(libro) {
            this.queryLibro = libro.titulo;
            this.$emit('libroSelect', libro);
        }
    }
}
</script>

<style>

</style>