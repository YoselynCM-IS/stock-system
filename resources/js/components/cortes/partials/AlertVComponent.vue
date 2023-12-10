<template>
    <div>
        <b-alert show variant="secondary" class="mt-5">
            <i class="fa fa-warning"></i> No se han agregado {{ text }} al corte.
        </b-alert>
    </div>
</template>

<script>
export default {
    props: ['text']
}
</script>

<style>

</style>