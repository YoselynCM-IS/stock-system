<template>
    <div>
        <b-row class="mb-2">
            <b-col sm="8">
                <h6><b>Cliente:</b> {{ cliente_name }}</h6>
            </b-col>
            <b-col>
                <b-row>
                    <b-col class="text-right" sm="4"><h6><b>Creado por:</b></h6></b-col>
                    <b-col>{{ user_name }}</b-col>
                </b-row>
                <b-row>
                    <b-col class="text-right" sm="4"><h6><b>Creado el:</b></h6></b-col>
                    <b-col>{{ created_at | moment }}</b-col>
                </b-row>
            </b-col>
        </b-row>
    </div>
</template>

<script>
import moment from '../../../mixins/moment';
export default {
    props: ['cliente_name', 'user_name', 'created_at'],
    mixins: [moment]
}
</script>

<style>

</style>