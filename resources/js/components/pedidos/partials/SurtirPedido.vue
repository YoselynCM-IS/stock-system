<template>
    <div>
        <b-form @submit.prevent="saveSurtir()">
            <b-form-group label="Surtir pedido en">
                <b-form-select :disabled="load" v-model="form.nota" :options="notas" required></b-form-select>
            </b-form-group>
            <b-form-group label="Comentario">
                <b-form-textarea v-model="form.comentario" required rows="2" max-rows="2" style="text-transform:uppercase;"></b-form-textarea>
            </b-form-group>
            
            <b-button type="submit" :disabled="load"
                class="mt-2" variant="success" pill block>
                <i class="fa fa-check-circle"></i> Surtir
            </b-button>
        </b-form>
    </div>
</template>

<script>
import sweetAlert from '../../../mixins/sweetAlert';
export default {
    props: ['pedidoId'],
    mixins: [sweetAlert],
    data(){
        return {
            form: {
                pedido_id: null,
                nota: null,
                comentario: null
            },
            notas: [
                { value: null, text: 'Selecciona una opción', disabled: true },
                { value: 'remisiones', text: 'Remisión'},
                // { value: 'promocion', text: 'Promoción' },
            ],
            load: false
        }
    },
    created: function(){
        this.form.pedido_id = this.pedidoId;
    },
    methods: {
        // SURTIR PEDIDO CON LO SELECCIONADO
        saveSurtir(){
            this.load = true;
            axios.post(`/${this.form.nota}/surtir`, this.form).then(response => {
                if(!response.data) {
                    this.messageAlert('center', 'warning', 'El pedido no contiene lo necesario para crear lo que se seleccionó.', null, 'info');
                } else {
                    this.messageAlert('center', 'success', 'El pedido se surtio correctamente.', null, 'reload');
                }
                this.load = false;
            }).catch(error => {
                this.load = false;
            });
        }
    }
}
</script>

<style>

</style>