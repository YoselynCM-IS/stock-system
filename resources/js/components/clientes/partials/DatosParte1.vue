<template>
    <div>
        <b-row class="my-1">
            <b-col align="right">{{(form.tipo == null || form.tipo == 'CLIENTE') ? 'Plantel':'Distribuidor'}}</b-col>
            <div class="col-md-9">
                <b-form-input 
                    id="input-name"
                    style="text-transform:uppercase;"
                    v-model="form.name"
                    :disabled="load"
                    required>
                </b-form-input>
                <div v-if="errors && errors.name" class="text-danger">{{ errors.name[0] }}</div>
            </div>
        </b-row>
        <b-row class="my-1">
            <b-col align="right">{{(form.tipo == null || form.tipo == 'CLIENTE') ? 'Coordinador':'Comunicarse con'}}</b-col>
            <div class="col-md-9">
                <b-form-input 
                    id="input-name"
                    style="text-transform:uppercase;"
                    v-model="form.contacto"
                    :disabled="load">
                </b-form-input>
                <div v-if="errors && errors.contacto" class="text-danger">{{ errors.contacto[0] }}</div>
            </div>
        </b-row>
    </div>
</template>

<script>
export default {
    props: ['form', 'load', 'errors']
}
</script>