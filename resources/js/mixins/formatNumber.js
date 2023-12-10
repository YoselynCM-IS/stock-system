export default {
    filters: {
        formatNumber: function (value) {
            return numeral(value).format("0,0[.]00"); 
        }
    }
}