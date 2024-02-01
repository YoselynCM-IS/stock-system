<template>
    <div>
        <b-input style="text-transform:uppercase;"
            v-model="titulo" autofocus
            @keyup="librosScratch(titulo)" placeholder="BUSCAR LIBRO"
        ></b-input>
        <div class="list-group" v-if="resultsScratch.length" id="listaL">
            <a class="list-group-item list-group-item-action" 
                href="#" v-bind:key="i" 
                v-for="(libro, i) in resultsScratch" 
                @click="selectScratch(libro)">
                PACK: <b>{{ libro.lf_titulo }}</b> / <b>{{ libro.ld_titulo }}</b>
            </a>
        </div>
    </div>
</template>

<script>
import busquedaScratch from '../../../mixins/libros/busquedaScratch';
export default {
    mixins: [busquedaScratch],
    data() {
        return {
            titulo: null
        }    
    },
    methods: {
        // ENVIAR DATOS DEL PACK SELECCIONADO
        selectScratch(pack) {
            this.titulo = `PACK: ${pack.lf_titulo}`;
            this.resultsScratch = [];
            this.$emit('assignScracth', pack);
        }
    }
}
</script>

<style>

</style>