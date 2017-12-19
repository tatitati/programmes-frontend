define('timezone-notification', ['jquery-1.9', 'rv-bootstrap'], function ($) {
    // Cheating as we don't need any code from rv-bootstrap, but we do trigger an event that it defines
    "use strict";

    /**
     * Extending Date() object
     */
    Date.prototype._stdTimezoneOffset = function () {
        var jan = new Date(this.getFullYear(), 0, 1);
        var jul = new Date(this.getFullYear(), 6, 1);
        return Math.max(jan.getTimezoneOffset(), jul.getTimezoneOffset());
    };
    Date.prototype._dst = function () {
        return this.getTimezoneOffset() < this._stdTimezoneOffset();
    };

    /** @constructor */
    var TimezoneNotification = function (options) {
        var dateRequestedByUser = this.extractDateRequestedByUser();
        this.gmtOffset = this.getGmtOffset(dateRequestedByUser);

        this.setOptions(options);
        this.attachCustomEvent();
        this.loadNotifications();
        this.reloadSchedules();
    };

    TimezoneNotification.prototype = {
        options: {
            translations: {
                localTime: 'Local time',
                localMonths: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                localDaysOfWeek: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']
            },
            attrTargetAddUtcOffset: 'data-href-add-utcoffset',
            attrPageTime: 'data-page-time',
            cssSelectors: {
                startDate: '[data-timezone]',
                offsetReload: '[data-utcoffset-replace]',
                offsetRewrite: '[data-href-add-utcoffset]',
                timezone: 'timezone',
                timeNote: '.timezone-gmt'
            },
            showGmtOffset: false
        },
        /**
         * Merge custom options with default
         * @param {Object} options - Options values to override
         */
        setOptions: function (options) {
            this.options = $.extend(true, {}, this.options, options);
        },
        attachCustomEvent: function () {
            var _this = this;
            $(document).on("lazyloadComplete", function (e, params) {
                if (params && params.content) {
                    _this.loadNotifications(params.content);
                    return;
                }
                _this.loadNotifications();
            });
        },
        /**
         * Find elements with "data-timezone" attribute
         * @param {Jquery} [content] - Context to search for elements with this attribute
         * @returns {*|jQuery|HTMLElement}
         */
        getTimeElements: function (content) {
            var elements = (content) ? content.find(this.options.cssSelectors.startDate) : $(this.options.cssSelectors.startDate);
            return elements;
        },
        loadNotifications: function (content) {
            var _this = this,
                items = (content) ? _this.getTimeElements(content) : _this.getTimeElements(),
                timestamp, currentItem, currentDateTime, visitorDate,
                dayDate = false;

            items.each(function () {
                currentItem = $(this);
                currentDateTime = currentItem.attr('content');
                timestamp = Date.parse(currentDateTime);

                // bail if it wasn't parsable (apparently this fixes an IE bug?!)
                if (timestamp !== timestamp) {
                    return;
                }
                visitorDate = new Date();
                visitorDate.setTime(timestamp);

                if (currentItem.find('.' + _this.options.cssSelectors.timezone + '--date').length > 0) {
                    dayDate = _this.formatDateString(visitorDate);
                }
                // rewrite emission times of each programme
                _this.createNotificationElements(currentItem, visitorDate, dayDate);
            });
            this.rewriteScheduleLinks(content);
        },
        /**
         * Transform Date to readable format "Thu 26 Oct 2017"
         * @param {Date} visitorDate
         * @returns {string}
         */
        formatDateString: function (visitorDate) {
            var day = this.options.translations.localDaysOfWeek[visitorDate.getDay()];
            var month = this.options.translations.localMonths[visitorDate.getMonth()];
            var date = visitorDate.getDate();
            var year = visitorDate.getFullYear();
            var dayDate = day + " " + date + " " + month + " " + year;
            return dayDate;
        },
        /**
         * Convert Date to string in format ("-05:34" for example).
         * @param {Date} [userDate] - Date we use to display offset
         * @returns {string}
         */
        getGmtOffset: function (userDate) {
            userDate = userDate || new Date();
            var offset = userDate.getTimezoneOffset();
            var sign = '-';
            if (offset < 0) {
                sign = '+';
                offset *= -1;
            }
            if (offset < 1) {
                return '';
            }
            var hours = Math.floor(offset / 60);
            var minutes = parseInt(offset - (hours * 60));
            // Return a string in the form '+01:00'
            return ('' + sign + this.zeroPad(hours) + ':' + this.zeroPad(minutes));
        },
        /**
         * Convert for instance 2 to "02"
         * @param {number} num
         * @returns {string}
         */
        zeroPad: function (num) {
            var numZeros = 2;
            var n = Math.abs(num);
            var zeros = Math.max(0, numZeros - Math.floor(n).toString().length);
            var zeroString = Math.pow(10, zeros).toString().substr(1);
            return zeroString + n;
        },
        createNotificationElements: function (holder, visitorDate, day) {
            if (!holder || !visitorDate) return false;

            var noteContainer = '', timeContainer = '', dateContainer = '', gmtHolder = '';

            var hours = ("0" + visitorDate.getHours()).slice(-2);
            var minutes = ("0" + visitorDate.getMinutes()).slice(-2);
            var time = hours + ":" + minutes;

            /* return gmtOffset e.g. (GMT+8) */
            if (this.options.showGmtOffset) {
                gmtHolder = '<span class="' + this.options.cssSelectors.timezone + '--note__gmt">(GMT' + this.getGmtOffset(visitorDate) + ')</span>';
            }

            noteContainer = '<span class="' + this.options.cssSelectors.timezone + ' ' + this.options.cssSelectors.timezone + '--note"> ' + this.options.translations.localTime + ' ' + gmtHolder + '</span>';

            holder.find('.' + this.options.cssSelectors.timezone + '--time').html(time + ' ' + noteContainer);
            holder.find('.' + this.options.cssSelectors.timezone + '--date').html(day);
        },
        /**
         * Append utcoffset param to url
         * @param {string} url - url to append the new utcoffset=<offset> value
         * @param {string} [offset] - expected format example "+13:02"
         */
        rewriteUrlWithUtcOffset: function (url, offset) {
            var offset = offset || this.gmtOffset;
            var sep = (url.indexOf('?') != -1) ? '&' : '?';
            return url + sep + 'utcoffset=' + encodeURIComponent(offset);
        },
        reloadSchedules: function (content) {
            // if user requests day in timezone != GMT, reload
            if (!this.gmtOffset) {
                return;
            }
            var replaceDivs = (content) ? content.find(this.options.cssSelectors.offsetReload) : $(this.options.cssSelectors.offsetReload);
            var _this = this;
            replaceDivs.each(function () {
                var $this = $(this);
                var url = $this.attr('data-utcoffset-replace');
                if (url) {
                    // Create a new lazyload for lazyload.js
                    $this.attr('data-lazyload-inc', _this.rewriteUrlWithUtcOffset(url));
                    $this.attr('data-lazyload-always', 'true');
                    $this.addClass('lazy-module');
                }
            });
            // Trigger lazyload.js to load in any newly created lazyloads
            $('body').trigger('lazyload-refresh');
        },
        /**
         * rewrite url of links containing "schedules/<pid>/yyyy/mm/dd" with the new utcoffset param
         */
        rewriteScheduleLinks: function (content) {
            var scheduleLinks = (content) ? content.find(this.options.cssSelectors.offsetRewrite) : $(this.options.cssSelectors.offsetRewrite);
            var _this = this;
            scheduleLinks.each(function () {
                var $this = $(this);
                var href = $this.attr('href');
                // Here look for links of the format /schedules/pid/yyyy/mm/dd and get local time from yyyy mm dd
                // and put into offset
                if (href) {
                    var offset = null;
                    var extractedDate = _this.extractDateFromSchedulesLink(href);
                    if (extractedDate instanceof Date) {
                        offset =  _this.getGmtOffset(extractedDate);
                    }

                    // append ?utcoffset=xx:yy to any relevant URLs to prevent a double load
                    // we only want to rewrite links that has different utcoffset. The utcoffset that will
                    // be added will be the given by the link date.
                    if (offset) {
                        $this.attr('href', _this.rewriteUrlWithUtcOffset(href, offset));
                    }

                    $this.removeAttr(_this.options.attrTargetAddUtcOffset);
                }
            });
        },
        /**
         * Check if url link has a date yyyy/mm/dd, and convert it to Date
         * @param {string} href - Expected format: http...../schedules/<pid>/yyyy/mm/dd
         * @returns {(Date|null)}
         */
        extractDateFromSchedulesLink: function (href) {
            var urlHrefRegex = new RegExp('schedules\/[0-9b-df-hj-np-tv-z]{8,15}\/([0-9]{4}\/[0-9]{2}\/[0-9]{2})$');
            var matches = urlHrefRegex.exec(href);

            if (matches && matches.length == 2) {
                var matchedDateGroup = matches[1];
                return this.transformStringToDate(matchedDateGroup);
            }

            return null;
        },
        /**
         * Extract attribute "data-page-time" that store the requested date by the
         * user in format "yyyy/mm/dd" and convert it to Date
         * @returns {(Date|null)}
         */
        extractDateRequestedByUser: function() {
            var domElementsWithPageTime = $('[' + this.options.attrPageTime + ']');
            if (domElementsWithPageTime.length == 0) {
                return null;
            }

            var $requestedDateByUser = $(domElementsWithPageTime[0]);
            return this.transformStringToDate($requestedDateByUser.attr(this.options.attrPageTime));
        },
        /**
         * Convert string (in format "yyyy/mm/dd") to Date
         * @param {string} stringWithDate - Expected format yyyy/mm/dd
         * @returns {(Date|null)}
         */
        transformStringToDate: function(stringWithDate) {
            var dateRegex = new RegExp('[0-9]{4}\/[0-9]{2}\/[0-9]{2}');
            if (dateRegex.test(stringWithDate)) {
                var pieces = stringWithDate.split('/');
                var year = parseInt(pieces[0], 10);
                var month = parseInt(pieces[1], 10) - 1 ; // jquery months starts in zero
                var day = parseInt(pieces[2], 10);

                return new Date(year, month, day);
            }

            return null;
        }
    };
    return TimezoneNotification;
});
