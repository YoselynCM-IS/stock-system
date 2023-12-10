<template>
    <div>
        <b-row>
            <b-col>
                <b-row class="mb-3">
                    <b-col sm="2" class="text-right">
                        <label for="input-numero">Folio</label>
                    </b-col>
                    <b-col>
                        <b-form-input id="input-numero" type="number" v-model="queryFolio"></b-form-input>
                    </b-col>
                    <b-col>
                        <b-button variant="dark" @click="searchRemisiones()" pill size="sm"
                            :disabled="load">
                            <i class="fa fa-search"></i> Buscar
                        </b-button>
                    </b-col>
                </b-row>
            </b-col>
            <b-col>
                <b-row>
                    <b-col><h6><b>Remisiones seleccionadas</b></h6></b-col>
                    <b-col sm="3">
                        <b-button variant="success" pill block @click="guardarRelacion()"
                            :disabled="load || form.selected.length == 0">
                            <i class="fa fa-check"></i> Guardar
                        </b-button>
                    </b-col>
                </b-row>
            </b-col>
        </b-row>
        <b-row>
            <b-col>
                <div v-if="!load">
                    <b-table v-if="remisiones.length > 0" :items="remisiones" :fields="fields">
                        <template v-slot:cell(actions)="data">
                            <b-button variant="secondary" pill @click="selectRemision(data.item)" size="sm"
                                :disabled="load">
                                <i class="fa fa-check"></i>
                            </b-button>
                        </template>
                    </b-table>
                    <no-registros-component v-else></no-registros-component>
                </div>
                <load-component v-else></load-component>
            </b-col>
            <b-col>
                <b-table v-if="form.selected.length > 0" :items="form.selected" :fields="fields">
                    <template v-slot:cell(actions)="data">
                        <b-button variant="secondary" pill @click="quitarRemision(data.item, data.index)" size="sm"
                            :disabled="load">
                            <i class="fa fa-minus"></i>
                        </b-button>
                    </template>
                </b-table>
                <no-registros-component v-else></no-registros-component>
            </b-col>
        </b-row>
    </div>
</template>

<script>
import NoRegistrosComponent from '../funciones/NoRegistrosComponent.vue';
import toast from '../../mixins/toast';
import LoadComponent from '../cortes/partials/LoadComponent.vue';
import sweetAlert from '../../mixins/sweetAlert';
export default {
    props: ['order_id'],
    components: { NoRegistrosComponent, LoadComponent },
    mixins: [toast, sweetAlert],
    data(){
        return {
            queryFolio: null,
            remisiones: [],
            load: false,
            fields: [
                { key: 'id', label: 'Folio' },
                // { key: 'fecha_creacion', label: 'Fecha de creación' },
                { key: 'cliente.name', label: 'Cliente' },
                { key: 'actions', label: '' }
            ],
            form: {
                order_id: null,
                selected: []
            }
        }
    },
    methods: {
        searchRemisiones(){
            this.load = true;
            this.remisiones = [];
            axios.get('/buscar_por_numero', {params: {num_remision: this.queryFolio}}).then(response => {
                if(response.data.remision){
                    this.remisiones.push(response.data.remision);
                }                
                this.load = false;
            }).catch(error => {
                this.load = false;
            });
        },
        selectRemision(remision){
            if(this.form.selected.find( s => s.id === remision.id ) == undefined){
                this.form.selected.push(remision);
            } else {
                this.makeToast('warning', 'La remisión ya ha sido agregada');
            }
        },
        quitarRemision(remision, index){
            this.form.selected.splice(index, 1);
        },
        guardarRelacion(){
            this.load = true;
            this.form.order_id = this.order_id;
            axios.post('/order/relacionar', this.form).then(response => {
                this.load = false;
                this.messageAlert('center', 'success', 'Las remisiones se relacionaron correctamente.', null, 'reload');
            }).catch(error => {
                this.load = false;
            });
        }
    }
}
</script>

<style>

</style>