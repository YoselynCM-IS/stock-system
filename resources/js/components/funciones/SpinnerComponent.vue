<template>
    <div>
        <b-spinner v-if="load" type="grow" label="Loading..."></b-spinner> 
        <i v-else class="fa fa-check-circle"></i>
        {{ !load ? 'Guardar':'Guardando' }}
    </div>
</template>

<script>
export default {
    props: ['load']
}
</script>

<style>

</style>