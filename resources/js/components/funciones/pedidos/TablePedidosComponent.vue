<template>
    <div>
        <b-table :items="form.libros" :fields="fields">
            <template v-slot:cell(index)="data">
                {{ data.index + 1 }}
            </template>
            <template v-slot:cell(quantity)="data">
                {{ data.item.quantity | formatNumber }}
            </template>
            <template v-slot:cell(price)="data">
                ${{ data.item.price | formatNumber }}
            </template>
            <template v-slot:cell(total)="data">
                ${{ data.item.total | formatNumber }}
            </template>
            <template v-slot:cell(edit)="data">
                <div v-if="tipo !== 2">
                    <b-button variant="warning" pill @click="edit_register(data.item, data.index)"
                        :disabled="load">
                        <i v-if="!editar2 || data.index !== position" class="fa fa-edit"></i>
                            <i v-if="editar2 && data.index == position" class="fa fa-spinner"> Editando</i>
                    </b-button>
                    <b-button variant="danger" pill @click="delete_register(data.item, data.index)"
                        :disabled="load">
                        <i class="fa fa-trash"></i>
                    </b-button>
                </div>
                <div v-if="tipo == 2 && data.item.libro.type == 'digital' && data.item.tipo == 'alumno'">
                    <b-button v-if="data.item.pack_id == null" :disabled="loadScratch"
                        variant="dark" pill size="sm" @click="assignScratch(data.item, data.index)">
                        Scratch
                    </b-button>
                    <b-badge v-else variant="success"><i class="fa fa-check-circle"></i></b-badge>
                </div>
            </template>
            <template #thead-top="row" v-if="tipo !== 2">
                <tr>
                    <th><b>{{ !editar2 ? 'Agregar':'Editar' }}</b></th>
                    <th>ISBN</th>
                    <th>Titulo</th>
                    <th></th>
                    <th>Cantidad</th>
                    <th>Precio</th>
                    <th></th>
                    <th></th>
                </tr>
                <tr>
                    <th>
                        <b-button variant="secondary" pill block size="sm"
                            :disabled="(load || registro.libro.id == null)"
                            @click="inicializar_registro()">
                            Limpiar
                        </b-button>
                    </th>
                    <th>
                        <b-input
                            v-model="queryISBN" @keyup="buscarISBN()" :disabled="load || (editar2 && tipo == 1)"
                        ></b-input>
                        <div class="list-group" v-if="resultsISBNs.length" id="listaL">
                            <a class="list-group-item list-group-item-action" 
                                v-for="(libro, i) in resultsISBNs" v-bind:key="i"
                                @click="datosLibro(libro)" href="#" >
                                {{ libro.ISBN }}
                            </a>
                        </div>
                    </th>
                    <th>
                        <b-input style="text-transform:uppercase;"
                            v-model="queryTitulo" :disabled="load || (editar2 && tipo == 1)"
                            @keyup="getLibros(queryTitulo)"
                        ></b-input>
                        <div class="list-group" v-if="resultslibros.length" id="listaL">
                            <a class="list-group-item list-group-item-action" 
                                v-for="(libro, i) in resultslibros" v-bind:key="i" 
                                @click="datosLibro(libro)" href="#" >
                                {{ libro.titulo }}
                            </a>
                        </div>
                    </th>
                    <th>
                        <b-form-select v-if="registro.libro.type == 'digital'" v-model="registro.tipo" :options="code_tipos"
                                    required :disabled="load || (editar2 && tipo == 1)"></b-form-select>
                    </th>
                    <th>
                        <b-input required type="number" v-model="registro.quantity" :disabled="load"></b-input>
                    </th>
                    <th>
                        <b-input required type="number" v-model="registro.price" :disabled="load"></b-input>
                    </th>
                    <th></th>
                    <th>
                        <b-button variant="success" pill size="sm" 
                            :disabled="(load || registro.libro.id == null)" 
                            @click="save_register()">
                            <i class="fa fa-level-down"></i>
                        </b-button>
                    </th>
                </tr>
                <tr class="mt-5">
                    <th colspan="4"></th>
                    <th>
                        <b>{{ form.total_quantity | formatNumber }}</b>
                    </th>
                    <th></th>
                    <th>
                        <b>${{ form.total | formatNumber }}</b>
                    </th>
                    <th></th>
                </tr>
            </template>
        </b-table>
    </div>
</template>

<script>
import formatNumber from '../../../mixins/formatNumber';
import getLibros from '../../../mixins/getLibros';
import toast from '../../../mixins/toast';
export default {
    props: ['load', 'ftotalq', 'ftotal', 'flibros', 'tipo'],
    mixins: [formatNumber, getLibros, toast],
    data(){
        return {
            form: {
                total_quantity: 0,
                total: 0,
                libros: [],
                scratch: []
            },
            fields: [
                {label: 'N.', key: 'index'},
                {label: 'ISBN', key: 'libro.ISBN'},
                {label: 'Titulo', key: 'libro.titulo'},
                {label: '', key: 'tipo'},
                {label: 'Cantidad', key: 'quantity'},
                {label: 'Precio', key: 'price'},
                {label: 'Total', key: 'total'},
                {label: '', key: 'edit'}
            ],
            editar2: false,
            position: null,
            registro: {
                id: null,
                pack_id: null,
                libro: { id: null, ISBN: '', titulo: '', type: null},
                tipo: null, 
                quantity: 0,
                price: 0,
                total: 0
            },
            queryTitulo: null,
            code_tipos: [
                {value: null, text: 'Seleccionar', disabled: true},
                {value: 'alumno', text: 'alumno'},
                {value: 'demo', text: 'demo'},
                {value: 'profesor', text: 'profesor'}
            ],
            loadScratch: false
        }
    },
    created: function (){
        this.form.libros = this.flibros;
        this.form.total = this.ftotal;
        this.form.total_quantity = this.ftotalq;
    },
    methods: {
        datosLibro(libro){
            this.registro.tipo = null;
            this.assign_datos(libro);
            this.resultslibros = [];
            this.resultsISBNs = [];
        },
        edit_register(register, index){
            this.registro.id = register.id;
            this.registro.quantity = register.quantity;
            this.registro.price = register.price;
            this.registro.total = register.total;
            this.registro.tipo = register.tipo;
            this.assign_datos(register.libro);
            this.position = index;
            this.editar2 = true;
        },
        delete_register(register, index){
            this.form.libros.splice(index, 1);
            this.form.total_quantity = this.form.total_quantity - register.quantity;
            this.form.total = this.form.total - register.total;
            this.inicializar_registro();
            this.$emit('sendPedidos', this.form);
        },
        save_register(){
            if((this.registro.libro.id != null && this.registro.libro.type !== 'digital') || (this.registro.libro.type == 'digital' && this.registro.tipo !== null)){
                var check = true;
                const mismo_libro = this.form.libros.filter(p => (p.libro.id == this.registro.libro.id && p.tipo == this.registro.tipo));
                // VERIFICAR QUE EL LIBRO NO ESTE AGREGADO EN LA LISTA
                if((!this.editar2 && mismo_libro.length > 0) || (this.editar2 && mismo_libro.length > 1) ) check = false;

                if(check){
                    if(this.registro.quantity > 0 && parseFloat(this.registro.price) >= 0){
                        this.registro.total = parseInt(this.registro.quantity) * parseFloat(this.registro.price);
                        if(!this.editar2){
                            this.form.libros.push(this.registro);
                        } else{
                            this.form.libros[this.position].id = this.registro.id;
                            this.form.libros[this.position].quantity = this.registro.quantity;
                            this.form.libros[this.position].price = this.registro.price;
                            this.form.libros[this.position].total = this.registro.total;
                            this.form.libros[this.position].libro.id = this.registro.libro.id;
                            this.form.libros[this.position].libro.ISBN = this.registro.libro.ISBN;
                            this.form.libros[this.position].libro.titulo = this.registro.libro.titulo;
                            this.form.libros[this.position].libro.type = this.registro.libro.type;
                            this.form.libros[this.position].tipo = this.registro.tipo;
                        }
                        this.inicializar_registro();

                        this.form.total_quantity = 0;
                        this.form.total = 0;
                        this.form.libros.forEach(registro => {
                            this.form.total_quantity += parseInt(registro.quantity);
                            this.form.total += parseFloat(registro.total);
                        });
                        this.$emit('sendPedidos', this.form);
                    } else {
                        this.makeToast('warning', 'Las unidades deben ser mayor a 0 y el precio igual o mayor a 0');
                    }
                } else {
                    this.makeToast('warning', 'El libro ya ha sido agregado.');
                }
            } else {
                this.makeToast('warning', 'No se ha seleccionado el libro.');
            }
        },
        inicializar_registro(){
            this.registro = { 
                id: null, pack_id: null, libro: { id: null, ISBN: '', titulo: '', type: null},
                quantity: 0, price: 0, total: 0, tipo: null
            };
            this.queryISBN = null;
            this.queryTitulo = null;
            this.position = null;
            this.editar2 = false;
        },
        assign_datos(libro){
            this.registro.libro.id = libro.id;
            this.registro.libro.ISBN = libro.ISBN;
            this.registro.libro.titulo = libro.titulo;
            this.registro.libro.type = libro.type;
            this.queryISBN = libro.ISBN;
            this.queryTitulo = libro.titulo;
        },
        // ASIGNAR SCRATCH
        assignScratch(peticion, position){
            this.loadScratch = true;
            axios.get(`/pedido/check_scratch`, {params: {peticion_id: peticion.id}}).then(response => {
                if(response.data.status){
                    this.form.libros[position].pack_id = response.data.resultado.pack_id;
                    this.form.scratch.push(response.data.resultado);
                    this.$emit('sendPedidos', this.form);
                } else{
                    this.makeToast('warning', 'No se encontró coincidencia con libro físico.');
                }
                
                this.loadScratch = false;
            }).catch(error => {
                this.loadScratch = true;
            });
        }
    }
}
</script>

<style>

</style>