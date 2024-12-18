import Swal from 'sweetalert2';
export default {
    methods: {
        messageAlert(position, icon, title, ruta, tipo){
            const swal = Swal.fire({
                position: position,
                icon: icon,
                title: title,
                showConfirmButton: true
            });

            if (tipo == 'close-opener') {
                swal.then((result) => {
                    window.close();
                    window.opener.document.location = ruta; 
                });
            }
            if (tipo == 'redirect') {
                swal.then((result) => {
                    location.href = ruta; 
                });
            }
            if (tipo == 'close') 
                swal.then((result) => window.close());
            if (tipo == 'reload') 
                swal.then((result) => location.reload());
        },
        messageOptions(title, confirmText, cancelText) {
            return Swal.fire({
                title: title,
                showDenyButton: true,
                confirmButtonText: confirmText,
                denyButtonText: cancelText
            });
        }
    },
}