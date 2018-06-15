define(['../playables'], function(playables) {

    var audio;

    var driver = {

        init: function() {
            audio = new Audio();
        },

        destroy: function() {
            audio = null;
        },

        play: function(playable, startTime) {

            if (playable instanceof playables.ClipAudioSource)
                throw new Error('Cannot play clip');

            audio.src = playable.getSrc();
            audio.load();
            audio.play();
            if (startTime) {
                audio.currentTime = startTime;
            }
            return this;
        },

        pause: function() {
            audio.pause();
            return this;
        },

        resume: function() {
            audio.play();
            return this;
        },

        stop: function() {
            audio.pause();
            return this;
        },

        setVolume: function(n) {
            audio.volume = n;
            return this;
        },

        getDuration: function() {
            return audio.duration;
        },

        getCurrentTime: function() {
            return audio.currentTime;
        },

        setCurrentTime: function(time) {
            audio.currentTime = time;
            return this;
        },

        on: function(eventName, listener) {
            audio.addEventListener(eventName, listener);
            return this;
        },

        off: function(eventName, listener) {
            audio.removeEventListener(eventName, listener);
            return this;
        }

    };

    return driver;
});
