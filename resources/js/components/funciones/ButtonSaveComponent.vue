<template>
    <div>
        <div class="text-right">
            <b-button type="submit" variant="success" pill :disabled="load">
                <spinner-component :load="load"></spinner-component>
            </b-button>
        </div>
    </div>
</template>

<script>
import SpinnerComponent from './SpinnerComponent.vue';
export default {
    props: ['load'],
    components: {SpinnerComponent}
}
</script>

<style>

</style>