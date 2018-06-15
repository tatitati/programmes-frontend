define(function() {

    var locales = {
        'cy': {},
        'en': {
            'Play {0}': 'Play {0}',
            'Pause playback': 'Pause playback',
            'Stop playback': 'Stop playback',
            'Resume playback': 'Resume playback'
        },
        'ga': {},
        'gd': {}
    };
    var defaultCode = 'en';
    var locale = locales[defaultCode];

    return {
        setLocale: function(code) {
            if (!locales[code]) {
                throw new Error('Locale "' + code + '" has not been defined');
            }
            locale = locales[code];
        },

        t: function(key, arr) {
            if (!locale[key]) {
                throw new Error('Translation for "' + key + '" has not been defined');
            }

            return locale[key].replace(/\{(\d+)\}/, function(full, match) {
                var s = '';
                if (arr[match]) {
                    s = arr[match];
                }
                return s;
            });
        }
    };

});