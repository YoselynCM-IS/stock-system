<template>
    <div>
        <b-form @submit.prevent="onSubmit()">
            <b-form-group label="Rol">
                <b-form-select v-model="form.role_id" autofocus 
                    :disabled="load" :options="roles" required></b-form-select>
                <div v-if="errors && errors.role_id" class="text-danger" style="font-size: 12px">
                    {{ errors.role_id[0] }}
                </div>
            </b-form-group>
            <b-form-group label="Correo">
                <b-form-input v-model="form.email" :disabled="load" required type="email"></b-form-input>
                <div v-if="errors && errors.email" class="text-danger" style="font-size: 12px">
                    {{ errors.email[0] }}
                </div>
            </b-form-group>
            <b-form-group label="Nombre">
                <b-form-input v-model="form.name" :disabled="load" required id="input-name"></b-form-input>
                <div v-if="errors && errors.name" class="text-danger" style="font-size: 12px">
                    {{ errors.name[0] }}
                </div>
            </b-form-group>
            <b-form-group label="Usuario">
                <b-form-input v-model="form.user_name" :disabled="true" required></b-form-input>
                <div v-if="errors && errors.user_name" class="text-danger" style="font-size: 12px">
                    {{ errors.user_name[0] }}
                </div>
            </b-form-group>
            <b-row>
                <b-col>
                    <b-button variant="secondary" pill block size="sm" 
                            @click="generarUser()">Generar usuario</b-button>
                </b-col>
                <b-col></b-col>
            </b-row>
            <b-form-group label="Contraseña">
                <b-form-input v-model="form.password" :disabled="load" type="password"></b-form-input>
                <div v-if="errors && errors.password" class="text-danger" style="font-size: 12px">
                    {{ errors.password[0] }}
                </div>
            </b-form-group>
            <b-row class="mt-2">
                <b-col>
                    <b-button variant="secondary" pill block size="sm" 
                            @click="generarPass()">Generar contraseña</b-button>
                </b-col>
                <b-col>{{ aleatoria }}</b-col>
            </b-row>
            <div class="text-right">
                <b-button type="submit" :disabled="load" variant="success" pill>
                    <i class="fa fa-check"></i> {{ !load ? 'Guardar' : 'Guardando' }} <b-spinner small v-if="load"></b-spinner>
                </b-button>
            </div>
        </b-form>
    </div>
</template>

<script>
import sweetAlert from '../../mixins/sweetAlert';
export default {
    props: ['form', 'edit'],
    mixins: [sweetAlert],
    data() {
        return {
            load: false,
            roles: [],
            aleatoria: '',
            errors: {}
        }
    },
    created: function () {
        this.load = true;
        axios.get('/users/get_roles').then(response => {
            this.roles.push({ value: null, text: 'Seleccionar opción', disabled: true });
            response.data.forEach(rd => {
                this.roles.push({ value: rd.id, text: rd.rol });
            });
            this.load = false;
        }).catch(error => {
            this.load = false;
        });
    },
    methods: {
        // GUARDAR ACTUALIZACIÓN
        onSubmit() {
            this.load = true;
            if(this.edit) {
                axios.put('/users/update', this.form).then(response => {
                    this.messageAlert('center', 'success', 'El usuario se actualizo correctamente.', null, 'reload');
                    this.errors = {};
                    this.load = false;
                }).catch(error => {
                    this.load = false;
                    this.errors = error.response.data.errors || {};
                });
            } else {
                axios.post('/users/store', this.form).then(response => {
                    this.messageAlert('center', 'success', 'El usuario se creo correctamente.', null, 'reload');
                    this.errors = {};
                    this.load = false;
                }).catch(error => {
                    this.load = false;
                    this.errors = error.response.data.errors || {};
                });
            }
        },
        // GENERAR CONTRASEÑA ALEATORIA
        generarPass() {
            const banco = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789,;.:-_{}[]+*!#$%&/()=?¿¡@";
            this.aleatoria = "";
            for (let i = 0; i < 8; i++) {
                this.aleatoria += banco.charAt(Math.floor(Math.random() * banco.length));
            }
        },
        // GENERAR USUARIO
        generarUser() {
            this.load = true;
            axios.get('/users/set_user').then(response => {
                this.form.user_name = response.data;
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