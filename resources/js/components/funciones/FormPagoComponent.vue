<template>
    <div>
        <b-row>
            <b-col class="text-right" sm="4">
                <label>Pago</label>
            </b-col>
            <b-col sm="7">
                <b-form-input v-model="form.pago" autofocus :state="state" 
                            :disabled="load" required></b-form-input>
            </b-col>
        </b-row>
        <b-row>
            <b-col class="text-right" sm="4">
                <label>Fecha del pago</label>
            </b-col>
            <b-col sm="7">
                <b-form-input v-model="form.fecha"
                    type="date" :disabled="load" required>
                </b-form-input>
            </b-col>
        </b-row>
        <b-row>
            <b-col class="text-right" sm="4">
                <label>Nota</label>
            </b-col>
            <b-col sm="7">
                <b-form-textarea v-model="form.nota" 
                    :disabled="load" required
                    rows="6" max-rows="6">
                </b-form-textarea>
            </b-col>
        </b-row>
    </div>
</template>

<script>
export default {
    props: ['form', 'state', 'load']
}
</script>

<style>

</style>