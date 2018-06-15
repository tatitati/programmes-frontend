define(function() {

    return {
        merge: function(defaults, options) {
            var result = {};

            for (var key in defaults)
                result[key] = (key in options && options[key] != null) ? options[key] : defaults[key];

            return result;
        },

        isEmpty: function(obj) {
            for (var _ in obj) {
                return false;
            }
            return true;
        }
    }

});
