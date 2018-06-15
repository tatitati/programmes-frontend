define(['playlister/snippets/main', 'bump-3', 'istats-1'], function(main, bump, istats) {

    return {

        /**
         * Initialise snippets functionality
         * @param {Object} opts
         * @returns {snippets}
         */
        init: function(opts) {
            main.init(opts, bump, istats);
            return this;
        },

        create: function(ids, callback) {
            main.create(ids, callback);
            return this;
        },

        /**
         * Listen to API events.
         * @param {String} event
         * @param {Function} callback
         * @returns {snippets}
         */
        on: function(eventName, listener) {
            main.on(eventName, listener);
            return this;
        },

        /**
         * Stop listening to API events
         * @param {String} event
         * @param {Function} callback
         * @returns {snippets}
         */
        off: function(eventName, listener) {
            main.off(eventName, listener);
            return this;
        },

        /**
         * Control Snippets API.
         * @param {String} command
         * @param {*} args
         * @returns {*}
         */
        cmd: function(command, args) {
            return main.cmd(command, args);
        },

        /**
         * Returns the configuration settings object or null if not initialised
         * @returns Configuration|null
         */
        getConfig: function() {
            return main.getConfig();
        },

        /**
         * Returns the statistics object or null if not initialised
         * @returns stats.Statistics|null
         */
        getStats: function() {
            return main.getStats();
        },

        /**
         * Cleans up snippets
         * @returns {snippets}
         */
        destroy: function() {
            main.destroy();
            return this;
        }
    };

});
