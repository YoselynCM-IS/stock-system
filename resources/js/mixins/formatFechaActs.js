export default {
    filters: {
        moment: function (date) {
            return moment(date).format('DD MMMM YYYY, h:mm:ss a');
        },
    }
}