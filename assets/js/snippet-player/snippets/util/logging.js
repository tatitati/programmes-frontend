define(function() {

    function Logger() {
    }

    Logger.prototype.log = function(level, msg) {
        console[level]([new Date(), level.toUpperCase(), msg]);
    };

    Logger.prototype.info = function(msg) {
        return this.log('info', msg);
    };

    Logger.prototype.warn = function(msg) {
        return this.log('warn', msg);
    };

    Logger.prototype.error = function(msg) {
        return this.log('error', msg);
    };

    function NoOpLogger() {}

    NoOpLogger.prototype.log = function(level, msg) {};

    NoOpLogger.prototype.info = function(msg) {};

    NoOpLogger.prototype.warn = function(msg) {};

    NoOpLogger.prototype.error = function(msg) {};

    return {
        Logger: Logger,
        NoOpLogger: NoOpLogger
    };

})
