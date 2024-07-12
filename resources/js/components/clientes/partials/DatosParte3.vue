<template>
    <div>
        <b-row class="my-1">
            <b-col align="right">Tipo de cliente</b-col>
            <div class="col-md-9">
                <b-form-select v-model="form.tipo" :options="tipos" required
                    :disabled="load" autofocus
                ></b-form-select>
            </div>
        </b-row>
    </div>
</template>

<script>
export default {
    props: ['form', 'load'],
    data(){
        return {
            tipos: [
                { value: null, text: 'Selecciona una opción', disabled: true},
                { value: 'CLIENTE', text: 'CLIENTE' },
                { value: 'DISTRIBUIDOR', text: 'DISTRIBUIDOR' }
            ]
        }
    }
}
</script>