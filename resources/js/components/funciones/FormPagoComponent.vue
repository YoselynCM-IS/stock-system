<template>
    <div>
        <b-row class="mb-1">
            <b-col class="text-right" sm="4">
                <label>Pago</label>
            </b-col>
            <b-col sm="7">
                <b-form-input v-model="form.pago" autofocus :state="state" 
                            :disabled="load" required></b-form-input>
                <div v-if="errors && errors.pago" class="text-danger" style="font-size: 12px;">{{ errors.pago[0] }}</div>
            </b-col>
        </b-row>
        <b-row class="mb-1">
            <b-col class="text-right" sm="4">
                <label>Fecha del pago</label>
            </b-col>
            <b-col sm="7">
                <b-form-input v-model="form.fecha"
                    type="date" :disabled="load" required>
                </b-form-input>
                <div v-if="errors && errors.fecha" class="text-danger" style="font-size: 12px;">{{ errors.fecha[0] }}</div>
            </b-col>
        </b-row>
        <b-row class="mb-1">
            <b-col class="text-right" sm="4">
                <label>Nota</label>
            </b-col>
            <b-col sm="7">
                <b-form-textarea v-model="form.nota" 
                    :disabled="load" required
                    rows="6" max-rows="6">
                </b-form-textarea>
                <div v-if="errors && errors.nota" class="text-danger" style="font-size: 12px;">{{ errors.nota[0] }}</div>
            </b-col>
        </b-row>
    </div>
</template>

<script>
export default {
    props: ['form', 'state', 'load', 'errors']
}
</script>

<style>

</style>