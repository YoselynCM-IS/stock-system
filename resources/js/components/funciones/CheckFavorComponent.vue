<template>
    <div class="mt-3 mb-3 text-center">
        <p>El total de pagos excede el total pendiente del corte seleccionado, ¿desea continuar? El saldo sobrante será a favor del corte siguiente.</p>
        <b-row class="text-center">
            <b-col>
                <b-button pill size="sm" variant="success" @click="yes_continue()">
                    Si
                </b-button>
            </b-col>
            <b-col>
                <b-button pill size="sm" variant="danger" @click="not_continue()">
                    No
                </b-button>
            </b-col>
        </b-row>
    </div>
</template>

<script>
export default {
    methods: {
        // NO CONTINUAR CON SALDO A FAVOR
        not_continue(){
            this.$emit('answerCheck', 'no');
        },
        // SI CONTINUAR CON SALDO A FAVOR
        yes_continue(){
            this.$emit('answerCheck', 'yes');
        }
    }
}
</script>

<style>

</style>