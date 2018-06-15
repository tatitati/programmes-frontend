define(['../playables'], function(playables) {

    var container,
        audio,
        current,
        inited = false,
        paused = false;

    var mappings = {
        'ended': 'playlistEnded'
    };
    var mapEventName = function(eventName) {
        if (mappings[eventName])
            return mappings[eventName];
        return eventName;
    };

    var transformPlayable = function(playable) {
        if (playable instanceof playables.ClipAudioSource)
            return {
                kind: 'radioProgramme',
                vpid: playable.getId()
            };
        else
            return {
                kind: 'radioProgramme',
                href: playable.getSrc()
            };
    };

    var driver = {

        init: function(bump, additionalOptions) {
            var counterName = null,
                appName = null,
                bbcSite = null,
                appVersion = null,
                playlistType = null;
            if (typeof additionalOptions !== 'undefined') {
                if (typeof additionalOptions.counterName !== 'undefined') {
                    counterName = additionalOptions.counterName;
                }
                if (typeof additionalOptions.appVersion !== 'undefined') {
                    appVersion = additionalOptions.appVersion;
                }
                if (typeof additionalOptions.appName !== 'undefined') {
                    appName = additionalOptions.appName;
                }
                if (typeof additionalOptions.bbcSite !== 'undefined') {
                    bbcSite = additionalOptions.bbcSite;
                }
                if (typeof additionalOptions.playlistType !== 'undefined') {
                    playlistType = additionalOptions.playlistType;
                }
            }

            if (!inited) {
                container = document.createElement('div');
                container.setAttribute('class', 'spt-smp');
                document.body.appendChild(container);
                audio = bump(container).player({

                    ui: {

                        // Do not show UI elements
                        enabled: false,

                        // Prevents the display of the install flash message
                        // on IE
                        hideDefaultErrors: true
                    },

                    // The SMP player will fill the container rather than
                    // requiring width and height to be set
                    responsive: true,

                    // Tell the SMP that it should attempt playback in page
                    // rather than through a separate player application
                    preferInlineAudioPlayback: true,

                    // Workaround to prevent loading the BBC media player
                    // on Android prior to Kitkat
                    preferHtmlOnMobile: true,

                    playlistObject: {
                        items: [{
                            kind: 'radioProgramme',
                            href: 'http://emp.bbci.co.uk/emp/media/blank.mp3'
                        }]
                    },

                    autoplay: false,
                    // Additional data for DAx reporting
                    appName: appName,
                    appType: "responsive",
                    counterName: counterName,
                    statsObject: {
                        sessionLabels: {
                            app_version: appVersion,
                            bbc_site: bbcSite,
                            playlist_type: playlistType
                        }
                    }
                });

                audio.bind('error', function(err) {
                    var kpis = {
                        'critical': 'SMP_Critical',
                        'error': 'SMP_Error',
                        'warning': 'SMP_Warning'
                    };

                });

                audio.load();
            }

            // Indicate that the driver is initialised and should not be
            // reinitialised until the driver is destroyed
            inited = true;
        },

        destroy: function() {

            if (inited) {
                document.body.removeChild(container);
                container = null;

                audio.unbind('error');
                audio = null;
                current = null;
            }

            // Indicate that the driver has been destroyed and will need to
            // be reinitialised before use
            inited = false;
        },

        play: function(playable, startTime) {

            // Store a reference to the playable item currently being played,
            // this is used to indicate that the driver is in the correct state
            // to apply playback functions
            current = playable;

            audio.loadPlaylist({
                items: [transformPlayable(playable)]
            }, {
                autoplay: true,
                startTime: startTime || 0
            });
            paused = false;

            return this;
        },

        pause: function() {

            // Only attempt a pause on the SMP
            if (current) {
                audio.pause();
                paused = true;
            }
            return this;
        },

        resume: function() {
            if (current && paused) {
                audio.play();
                paused = false;
            }
            return this;
        },

        stop: function() {

            // Must check if paused otherwise SMP will throw an error:
            // Uncaught Error: Error: An invalid exception was thrown.
            if (current && !paused) {
                audio.suspend();
                current = null;
            }
            return this;
        },

        setVolume: function(n) {
            audio.volume(n);
            return this;
        },

        getDuration: function() {
            return audio.duration();
        },

        getCurrentTime: function() {
            return audio.currentTime();
        },

        setCurrentTime: function(time) {
            audio.currentTime(time);
            return this;
        },

        on: function(eventName, listener) {
            audio.bind(mapEventName(eventName), listener);
            return this;
        },

        off: function(eventName, listener) {
            audio.unbind(mapEventName(eventName), listener);
            return this;
        }

    };

    return driver;
});
