<template>
    <div>
        <b-alert v-if="!onLine" show variant="warning">
            <b>No tienes conexión a internet.</b><br>
            No se podrán guardar los cambios que has realizado, primero comprueba tu conexión a internet para continuar.
        </b-alert>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                onLine: navigator.onLine,
                showBackOnline: false
            }
        },
        methods: {
            // COMPROBAR LA CONEXIÓN A INTERNET
            updateOnlineStatus(e) {
                const { type } = e;
                this.onLine = type === 'online';
            },
        },
        watch: {
            onLine(v) {
                if(v) {
                    this.showBackOnline = true;
                    setTimeout(()=>{ this.showBackOnline = false; }, 1000);
                }
            }
        },
        mounted() {
            window.addEventListener('online',  this.updateOnlineStatus);
            window.addEventListener('offline', this.updateOnlineStatus);
        },
        beforeDestroy() {
            window.removeEventListener('online',  this.updateOnlineStatus);
            window.removeEventListener('offline', this.updateOnlineStatus);
        }
    }
</script>