moment.locale('es');
export default {
    filters: {
        moment: function (date) {
            return moment(date).format('DD-MM-YYYY');
        }
    }
}