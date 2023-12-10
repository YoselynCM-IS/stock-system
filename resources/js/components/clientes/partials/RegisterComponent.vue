<template>
    <div>
        <b-form @submit.prevent="onSubmit">
            <b-row>
                <b-col sm="6">
                    <b-form-group label="Registrar:">
                        <b-form-select v-model="form.registro" :options="registros" :disabled="load" required
                            @change="setRespuestas()"></b-form-select>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row v-if="form.registro != null && form.registro != 'nota'">
                <b-col>
                    <b-form-group label="Tipo">
                        <b-form-select v-model="form.tipo" 
                            :options="(form.registro == 'llamada' || form.registro == 'visita') ? tipos1:tipos2" 
                            :disabled="load" required></b-form-select>
                    </b-form-group>
                </b-col>
                <b-col>
                    <b-form-group label="Respuesta">
                        <b-form-select v-model="form.respuesta" :options="respuestas" :disabled="load" required></b-form-select>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col>
                    <b-form-group label="Fecha">
                        <b-form-datepicker v-model="form.fecha" :disabled="load" required></b-form-datepicker>
                    </b-form-group>
                </b-col>
                <b-col>
                    <b-form-group label="Hora">
                        <b-form-timepicker v-model="form.hora" locale="en" :disabled="load" required></b-form-timepicker>
                    </b-form-group>
                </b-col>
                <b-col sm="5">
                    <b-form-group v-if="form.registro == 'llamada' || form.registro == 'visita'" 
                        label="Duración">
                        <b-row>
                            <b-col class="text-center">
                                <b-form-input v-model="form.duracion.horas" type="number" :disabled="load" required></b-form-input>
                                <label>horas</label>
                            </b-col>
                            <b-col class="text-center">
                                <b-form-input v-model="form.duracion.minutos" type="number" :disabled="load" required></b-form-input>
                                <label>minutos</label>
                            </b-col>
                            <b-col class="text-center">
                                <b-form-input v-model="form.duracion.segundos" type="number" :disabled="load" required></b-form-input>
                                <label>segundos</label>
                            </b-col>
                        </b-row>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-form-group label="Comentario">
                <b-form-textarea v-model="form.comentario" rows="3" max-rows="6"></b-form-textarea>
            </b-form-group>
            <div class="text-right">
                <b-button type="submit" variant="success" pill class="mt-2">
                    <i class="fa fa-check"></i> Guardar
                </b-button>
            </div>
        </b-form>
    </div>
</template>

<script>
export default {
    props: ['cliente_id'],
    data() {
        return {
            load: false,
            form: {
                registro: null,
                cliente_id: null,
                tipo: null,
                fecha: null,
                hora: null,
                duracion: {
                    horas: 0,
                    minutos: 0,
                    segundos: 0 
                },
                respuesta: null,
                comentario: null
            },
            registros: [
                {value: null, text: 'Selecciona una opción', disabled: true},
                {value: 'llamada', text: 'Llamada'},
                {value: 'mensaje', text: 'Mensaje'},
                {value: 'correo', text: 'Correo'},
                {value: 'visita', text: 'Visita'},
                {value: 'nota', text: 'Nota'},
            ],
            tipos1: [
                {value: null, text: 'Selecciona una opción', disabled: true},
                {value: 'recibida', text: 'Recibida'},
                {value: 'realizada', text: 'Realizada'}
            ],
            tipos2: [
                {value: null, text: 'Selecciona una opción', disabled: true},
                {value: 'recibido', text: 'Recibido'},
                {value: 'enviado', text: 'Enviado'}
            ],
            respuestas: [],
            sr: {value: 'sin respuesta', text: 'sin respuesta'},
            no: {value: 'ocupado', text: 'ocupado'},
            bv: {value: 'buzón de voz', text: 'buzón de voz'},
            lt: {value: 'llamar más tarde', text: 'llamar más tarde'},
            ne: {value: 'número equivocado', text: 'número equivocado'},
            ni: {value: 'no interesado', text: 'no interesado'},
            ci: {value: 'correo incorrecto', text: 'correo incorrecto'},
            ct: {value: 'comunicarse más tarde', text: 'comunicarse más tarde'},
            ns: {value: 'no se encontró', text: 'no se encontró'},
        }
    },
    methods: {
        onSubmit(){
            this.load = true;
            this.form.cliente_id = this.cliente_id;
            axios.post('/clientes/save_seguimiento', this.form).then(response => {
                this.load = false;
                this.$emit('addedSeguimiento', response.data);
            }).catch(error => {
                this.load = false;
            });
        },
        setRespuestas(){
            this.respuestas = [];
            this.respuestas.push(
                {value: null, text: 'Selecciona una opción', disabled: true},
                {value: 'atendió', text: 'atendió'}
            );
            if(this.form.registro == 'llamada') this.respuestas.push(this.sr,this.no,this.bv,this.lt,this.ne,this.ni);
            if(this.form.registro == 'correo') this.respuestas.push(this.sr,this.ni,this.ci);
            if(this.form.registro == 'mensaje') this.respuestas.push(this.sr,this.ne,this.ni,this.ct)
            if(this.form.registro == 'visita') this.respuestas.push(this.no, this.ni, this.ns);
        }
    }
}
</script>

<style>

</style>