/**
 * This module create and run a fully instantiated smp/smp object
 * This factory is necessary to allow variable JS dependencies
 * based on the page type (e.g. some pages do not have access to all these dependencies)
 */
define(['jquery-1.9'], function ($) {

    var SmpFactory = function() {

        var defaultOptions = {
            'uasConfig': {}
        };

        this.runSmp = function (factoryOptions, smpOptions) {
            var requiredItems = ['smp/smp'];
            factoryOptions = mergeOptions(factoryOptions);

            if (factoryOptions.uasConfig) {
                requiredItems.push('smp/uasService');
                requiredItems.push('smp/recommendationsBump');
            } else {
                requiredItems.push(null);
                requiredItems.push(null);
            }

            if (window.bbcdotcom && window.bbcdotcom.config) {
                var hasDotComAdverts = window.bbcdotcom.config.isAdsEnabled && window.bbcdotcom.config.isAdsEnabled();
                var hasDotComAnalytics = window.bbcdotcom.config.isAnalyticsEnabled && window.bbcdotcom.config.isAnalyticsEnabled();
                requiredItems.push(hasDotComAdverts ? 'bbcdotcom/av/emp/adverts' : null);
                requiredItems.push(hasDotComAnalytics ? 'bbcdotcom/av/emp/analytics' : null);
            }

            require(requiredItems, function (Smp, UasService, RecommendationsBump, adverts, analytics) {
                var smpUasService = null;
                var smprecbump = null;

                if (factoryOptions.uasConfig) {
                    smpUasService = new UasService(factoryOptions.uasConfig);
                    smpUasService.init();
                }

                if (RecommendationsBump) {
                    smprecbump = new RecommendationsBump();
                }

                smpOptions.bbcdotcomAdverts = adverts;
                smpOptions.bbcdotcomAnalytics = analytics;
                smpOptions.UAS = smpUasService;
                smpOptions.recBump = smprecbump;

                var smp = new Smp(smpOptions);
                smp.init();
            });
        };

        // private

        var mergeOptions = function (options) {
            return $.extend({}, defaultOptions, options);
        };

    };

    return SmpFactory;
});
