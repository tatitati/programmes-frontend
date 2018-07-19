define(['jquery-1.9','smp/cookieStore'], function ($, DataStore) {

    var SmpStatePersistance = function (programmePid) {
        var dataStore = new DataStore();
        var programmePid = programmePid;

        var COOKIE_USER_PREFS = 'ckps_progs_playbackCookie';
        var COOKIE_RESUME = 'ckps_progs_player_resume';

        var volume = 0.6;
        var subtitles = false;

        /*
         * @example of cache data:
         *
         * [
         *      {pid: ..., time: ...},
         *      {pid: ..., time: ...},
         *      ....
         * ]
         */
        var cache = [];

        this.findVolume = function () {
            var cookieObj = dataStore.findByName(COOKIE_USER_PREFS);
            if (cookieObj && typeof cookieObj['volume'] !== 'undefined') {
                return cookieObj['volume'];
            }

            return volume;
        };

        this.findTimeResume = function () {
            var timeToResume = dataStore.findByName(COOKIE_RESUME);
            if (timeToResume) {
                cache = timeToResume;

                for (var i = 0, len = cache.length; i < len; i++) {
                    if (cache[i].pid === programmePid) {
                        return cache[i].time;
                    }
                }
            }

            return 0;
        };

        this.updateVolume = function (volumeValue) {
            dataStore.persist(COOKIE_USER_PREFS, {
                'volume': volumeValue,
                'muted': volumeValue === 0,
                'subtitles': subtitles
            });
        };

        this.updateTimeResume = function (timestamp) {
            dataStore.persist(COOKIE_RESUME, replaceTimeResumeForProgramme(timestamp));
        };

        this.removeTimeResume = function () {
            dataStore.persist(COOKIE_RESUME, removeProgrammeFromCache());
        };


        // Privates

        var replaceTimeResumeForProgramme = function (newTimeResume) {
            removeProgrammeFromCache();

            // add a new time resume for the pid
            cache.push({pid: programmePid, time: newTimeResume});
            // keep in cache only time resumes about the last 10 programmes reproduced
            cache = cache.slice(-10);

            return cache;
        };

        var removeProgrammeFromCache = function() {
            for (var i = 0, len = cache.length; i < len; i++) {
                if (cache[i].pid === programmePid) {
                    cache.splice(i, 1);
                    return;
                }
            }
        }
    };

    return SmpStatePersistance;
});
