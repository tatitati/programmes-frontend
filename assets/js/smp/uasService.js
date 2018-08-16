define(['jquery-1.9', 'istats-1', 'idcta/idcta-1', 'uasclient'],function ($, istats, idcta, UasClient) {
    /**
     * Constructor the the UAS module
     * @constructor
     */
    var UasService = function(uasConfig) {
        this.client = null;
        this.active = false;
        this.failCount = 0; // Count how many times a UAS call has failed
        this.saved_time = 0;
        this.recommendationData = null;
        this.options = $.extend({}, {
            env: null,
            domainSuffix: "co.uk",
            apiKey: null,
            heartbeatFrequency: 30,
            failTimes: [
                120000, // 2 minutes
                300000, // 5 minutes
                600000  // 10 minutes
            ]
        }, uasConfig);

        this.init = function() {
            if (this.options.apiKey && userIsLoggedInAndHasTrackingEnabled()) {
                var self = this;
                self.active = true;
                self.client = UasClient;
                self.client.init(self.options);

                istats.isReady(function() {
                    self.recommendationData = getRecommendationDataFromIstats();
                    notifyLoadedPage();
                });
            }
        };

        this.notifyStarted = function(time) {
            if (isNaN(parseInt(time))) {
                time = 0;
            }

            sendToUas(formatData({
                heartbeat: "false",
                timeupdate: time,
                action: "started"
            }));
        };

        this.notifyPaused = function(time) {
            sendToUas(formatData({
                heartbeat: "false",
                timeupdate: time,
                action: "paused"
            }));
        };

        this.notifyEnded = function(time) {
            sendToUas(formatData({
                heartbeat: "false",
                timeupdate: time,
                action: "ended"
            }));
        };

        this.notifyHeartbeat = function(timeupdate) {
            if ((timeupdate % this.options.heartbeatFrequency) === 0  && timeupdate != this.saved_time) {
                this.saved_time = timeupdate;

                var uasData = {
                    heartbeat       : true,
                    activityType    : 'heartbeat',
                    resourceDomain  : this.options.resourceDomain,
                    resourceType    : this.options.resourceType,
                    resourceId      : this.options.pid,
                    action          : 'heartbeat',
                    actionContext   : "urn:bbc:" +
                    this.options.resourceDomain +
                    ":version_offset:" + this.options.versionPid +
                    "#" + timeupdate
                };

                if (self.recommendationData) {
                    uasData.metaData = JSON.stringify(self.recommendationData);
                }

                sendToUas(uasData);
            }
        };

        // private

        var self = this;

        var notifyLoadedPage = function() {
            if (self.options.resourceDomain == 'radio' && self.recommendationData) {
                var uasData = {
                    activityType    : 'reads',
                    resourceDomain  : self.options.resourceDomain,
                    resourceType    : self.options.resourceType,
                    resourceId      : 'urn:bbc:pips::pid:' + self.options.pid,
                    action          : 'read'
                };

                if (self.recommendationData) {
                    uasData.metaData = JSON.stringify(self.recommendationData);
                }

                sendToUas(formatData(uasData));
            }
        };

        var formatData = function(eventData) {
            var uasData = {
                activityType    : 'plays',
                resourceDomain  : self.options.resourceDomain,
                resourceType    : self.options.resourceType,
                resourceId      : self.options.pid,
                action          : eventData.action,
                actionContext   : "urn:bbc:" +
                self.options.resourceDomain +
                ":version_offset:" + self.options.versionPid +
                "#" + eventData.timeupdate
            };

            if (self.recommendationData) {
                uasData.metaData = JSON.stringify(self.recommendationData);
            }

            return uasData;
        };

        var sendToUas = function(uasData) {
            if (!self.active || self.client == null) {
                return;
            }

            var deferred = $.Deferred();

            self.client.create(uasData, function (err, res) {
                if (err) {
                    if (err.triggerRetry) {
                        err.triggerRetry();
                        return;
                    }
                    deferred.reject(err);
                    initCircuitBreaker();
                } else {
                    deferred.resolve("success");
                    self.active = true;
                    self.failCount = 0;
                }
            });

            return deferred.promise();
        };

        var userIsLoggedInAndHasTrackingEnabled = function () {
            if (idcta.hasCookie()) {
                var userDetails = idcta.getUserDetailsFromCookie();
                // for IDv4 the above method returns null. Remove the null check once IDv4 is history
                if (userDetails == null || userDetails.ep) {
                    return true;
                }
            }
            return false;
        };

        /**
         * Interrogate the iStats API to determine whether the user
         * has landed on this page via a recommendation. If so,
         * add that data to the UAS request.
         */
        var getRecommendationDataFromIstats = function() {
            var labelsSent = istats.labelsSent();
            if (!labelsSent) {
                return null;
            }

            for (var i = 0; i < labelsSent.length; i++) {
                var label = labelsSent[i];
                if (((typeof label === 'object') && label && label.prev_rec_set && label.prev_rec_source)) {
                    return {
                        recommendationSet: label.prev_rec_set,
                        engineId: label.prev_rec_source
                    };
                }
            }

            return null;
        };

        /**
         * Circuit breaker for if the UAS response is invalid
         */
        var initCircuitBreaker = function() {
            self.active = false;
            // Set timeout milliseconds depending on how many times it has failed before
            // After the specified amount of retries has finished, then it will stop retrying
            if (self.options.failTimes[self.failCount] !== undefined) {
                window.setTimeout(function() {
                    self.active = true;
                }, self.options.failTimes[self.failCount]);
            }

            self.failCount++;
        };
    };

    return UasService;
});
