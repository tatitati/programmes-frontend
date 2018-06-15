define(function() {

    function StatsPlayTimer() {
        this._start = null;
        this._duration = 0;
    }

    StatsPlayTimer.prototype.startTimer = function() {
        if (this._start == null) {
            this._start = new Date().getTime();
        }
        return this;
    };

    StatsPlayTimer.prototype.stopTimer = function() {
        if (this._start != null) {
            this._duration += new Date().getTime() - this._start;
            this._start = null;
        }
        return this;
    };

    StatsPlayTimer.prototype.getDuration = function() {
        var current = 0;

        if (this._start != null) {
            current = new Date().getTime() - this._start;
        }

        return this._duration + current;
    };

    function StatsPlayCounter() {
        this._count = 0;
    }

    StatsPlayCounter.prototype.increment = function() {
        this._count += 1;
    };

    StatsPlayCounter.prototype.getCount = function() {
        return this._count;
    };

    function StatsLogger(playCounter, continuousPlayCounter, playTimer) {
        this._playCounter = playCounter;
        this._continuousPlayCounter = continuousPlayCounter;
        this._playTimer = playTimer;
    }

    StatsLogger.prototype.logPlay = function() {
        this._playCounter.increment();
    };

    StatsLogger.prototype.logContinuousPlay = function() {
        this._continuousPlayCounter.increment();
    };

    StatsLogger.prototype.logStartPlayback = function() {
        this._playTimer.startTimer();
    };

    StatsLogger.prototype.logStopPlayback = function() {
        this._playTimer.stopTimer();
    };

    return {
        StatsPlayTimer: StatsPlayTimer,
        StatsPlayCounter: StatsPlayCounter,
        StatsLogger: StatsLogger
    };

});
