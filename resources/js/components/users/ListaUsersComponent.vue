<template>
    <div>
        <b-row class="mb-2">
            <b-col>
                <b-pagination v-model="currentPage" aria-controls="my-table"
                    :total-rows="users.length" :per-page="perPage">
                </b-pagination>
            </b-col>
            <b-col sm="2">
                <b-button variant="success" pill block @click="addUser()">
                    <i class="fa fa-plus-circle"></i> Crear usuario
                </b-button>
            </b-col>
        </b-row>
        <!-- LISTADO USERS -->
        <b-table :items="users" :fields="fields" :tbody-tr-class="rowClass" id="my-table"
            :per-page="perPage" :current-page="currentPage">
            <template v-slot:cell(index)="row">
                {{ row.index + 1 }}
            </template>
            <template v-slot:cell(actions)="row">
                <div v-if="row.item.deleted_at == null">
                    <b-button variant="dark" pill size="sm" @click="editUser(row.item)">
                        <i class="fa fa-pencil"></i>
                    </b-button>
                    <b-button variant="dark" pill size="sm" @click="deleteUser(row.item)">
                        <i class="fa fa-minus"></i>
                    </b-button>
                </div>
                <b-button v-else variant="dark" pill size="sm" @click="restoreUser(row.item)">
                    <i class="fa fa-refresh"></i>
                </b-button>
            </template>
        </b-table>
        <!-- EDITAR USUARIO -->
        <b-modal ref="modalUserAE" id="modal-user-ae" :title="`${edit ? 'Editar':'Crear'} usuario`" hide-footer>
            <edit-add-user-component :form="user" :edit="edit"></edit-add-user-component>
        </b-modal>
        <!-- ELIMINAR USUARIO -->
        <b-modal ref="modalDelete" hide-footer title="Dar de baja usuario" size="sm">
            <p>¿Estás seguro(a) de dar de baja al usuario?</p>
            <b-row>
                <b-col>
                    <b-button variant="danger" size="sm" pill block
                        @click="confirmDelete()">
                        Si
                    </b-button>
                </b-col>
                <b-col>
                    <b-button variant="secondary" size="sm" pill block
                        @click="closeModal()">
                        Cancelar
                    </b-button>
                </b-col>
            </b-row>
        </b-modal>
    </div>
</template>

<script>
import sweetAlert from '../../mixins/sweetAlert';
export default {
    mixins: [sweetAlert],
    data() {
        return {
            users: [],
            fields: [
                { key: 'index', label: 'N.' },
                { key: 'role.rol', label: 'Rol' },
                { key: 'user_name', label: 'Usuario' },
                { key: 'name', label: 'Nombre' },
                { key: 'email', label: 'Correo' },
                { key: 'actions', label: '' }
            ],
            load: false,
            user: {},
            edit: false,
            showDeleteAlert: false,
            currentPage: 1,
            perPage: 25
        }
    },
    created: function () {
        this.load = true;
        axios.get('/users/index').then(response => {
            this.users = response.data;
            this.load = false;
        }).catch(error => {
            this.load = false;
        });
    },
    methods: {
        // MODIFICAR USUARIO
        editUser(user) {
            this.user.id = user.id;
            this.user.role_id = user.role_id;
            this.user.name = user.name;
            this.user.user_name = user.user_name;
            this.user.email = user.email;
            this.user.password = '';
            this.edit = true;
            this.$refs['modalUserAE'].show();
        },
        // AGREGAR USUARIO
        addUser() {
            this.user.id = null;
            this.user.role_id = null;
            this.user.name = null;
            this.user.user_name = null;
            this.user.email = null;
            this.user.password = '';
            this.edit = false;
            this.$refs['modalUserAE'].show();
        },
        deleteUser(user) {
            this.user.id = user.id;
            this.$refs['modalDelete'].show();
        },
        closeModal() {
            this.$refs['modalDelete'].hide();
        },
        confirmDelete() {
            this.load = true;
            axios.delete('/users/delete', {params: {user_id: this.user.id}}).then(response => {
                this.messageAlert('center', 'success', 'El usuario se dio de baja correctamente.', null, 'reload');
                this.load = false;
            }).catch(error => {
                this.load = false;
            });
        },
        rowClass(item, type) {
            if (!item) return
            if (item.deleted_at != null) return 'table-danger'
        },
        restoreUser(user) {
            this.load = true;
            let form = { user_id: user.id };
            axios.put('/users/restore', form).then(response => {
                this.messageAlert('center', 'success', 'El usuario se restauro correctamente.', null, 'reload');
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