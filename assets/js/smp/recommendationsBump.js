define(['jquery-1.9', 'istats-1'], function ($, istats) {
        function RecommendationsBump () {
            this.init();
        };

        RecommendationsBump.prototype.init = function() {
            this.validStatsFields = [
                "link_location",
                "prev_content_name",
                "prev_content_count",
                "prev_rec_feed",
                "prev_rec_source",
                "prev_rec_alg",
                "prev_rec_position",
                "prev_content_position",
                "clip_id",
                "episode_id"
            ];
        };

        RecommendationsBump.prototype.getRecommendationStats = function()
        {
            var recommendationData = {};
            var labelsSent = istats.labelsSent();
            if (!labelsSent) {
                return null;
            }

            for (var i = 0; i < labelsSent.length; i++) {
                var labels = labelsSent[i];
                if (typeof labels === 'object') {
                    // search in block of labels if we can find
                    // the ones we are interested on
                    for (var index in this.validStatsFields) {
                        var validFieldName = this.validStatsFields[index];
                        if (labels.hasOwnProperty(validFieldName)) {
                            recommendationData[validFieldName] = labels[validFieldName];
                        }
                    }
                }
            }

            return recommendationData;
        };

        return RecommendationsBump;
    });

