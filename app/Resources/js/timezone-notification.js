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

    var TimezoneNotification = function (options) {
        this.gmtOffset = this.getGmtOffset();
        this.setOptions(options);
        this.attachCustomEvent();
        this.loadNotifications();
        this.reloadSchedules();
    };

    TimezoneNotification.prototype = {
        options: {
            translations: {
                localTime: 'Local time',
                localMonths: [
                    'Jan',
                    'Feb',
                    'Mar',
                    'Apr',
                    'May',
                    'Jun',
                    'Jul',
                    'Aug',
                    'Sep',
                    'Oct',
                    'Nov',
                    'Dec'
                ],
                localDaysOfWeek: [
                    'Sun',
                    'Mon',
                    'Tue',
                    'Wed',
                    'Thu',
                    'Fri',
                    'Sat'
                ]
            },
            attrTargetAddUtcOffset: 'data-href-add-utcoffset',
            cssSelectors: {
                startDate: '[data-timezone]',
                offsetReload: '[data-utcoffset-replace]',
                offsetRewrite: '[data-href-add-utcoffset]',
                timezone: 'timezone',
                timeNote: '.timezone-gmt'
            },
            showGmtOffset: false
        },
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
        getTimeElements: function (content) {
            var elements = (content) ? content.find(this.options.cssSelectors.startDate) : $(this.options.cssSelectors.startDate);
            return elements;
        },
        loadNotifications: function (content) {
            var _this = this,
                items = (content) ? _this.getTimeElements(content) : _this.getTimeElements(),
                timestamp, currentItem, currentDateTime, visitorDate, hours, minutes,
                dayDate = false;

            items.each(function () {
                currentItem = $(this);
                currentDateTime = currentItem.attr('content');
                timestamp = Date.parse(currentDateTime);

                // bail if it wasn't parsable
                if (timestamp !== timestamp) {
                    return;
                }
                visitorDate = new Date();
                visitorDate.setTime(timestamp);

                hours = ("0" + visitorDate.getHours()).slice(-2);
                minutes = ("0" + visitorDate.getMinutes()).slice(-2);

                if (currentItem.find('.' + _this.options.cssSelectors.timezone + '--date').length > 0) {
                    dayDate = _this.formatDateString(visitorDate);
                }
                _this.createNotificationElements(currentItem, hours + ':' + minutes, dayDate);
            });
            this.rewriteScheduleLinks(content);
        },
        formatDateString: function (visitorDate) {
            var day = this.options.translations.localDaysOfWeek[visitorDate.getDay()];
            var month = this.options.translations.localMonths[visitorDate.getMonth()];
            var date = visitorDate.getDate();
            var year = visitorDate.getFullYear();
            var dayDate = day + " " + date + " " + month + " " + year;
            return dayDate;
        },
        getGmtOffset: function () {
            var offset = new Date().getTimezoneOffset();
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
        zeroPad: function (num) {
            // pad a number as in sprintf('%02d', num);
            var numZeros = 2;
            var n = Math.abs(num);
            var zeros = Math.max(0, numZeros - Math.floor(n).toString().length);
            var zeroString = Math.pow(10, zeros).toString().substr(1);
            return zeroString + n;
        },
        createNotificationElements: function (holder, time, day) {
            if (!holder || !time) return false;

            var noteContainer = '', timeContainer = '', dateContainer = '', gmtHolder = '';

            if (!this.gmtOffset) {
                this.gmtOffset = this.getGmtOffset();
            }

            /* return gmtOffset e.g. (GMT+8) */
            if (this.options.showGmtOffset) {
                gmtHolder = '<span class="' + this.options.cssSelectors.timezone + '--note__gmt">(GMT' + this.gmtOffset + ')</span>';
            }

            noteContainer = '<span class="' + this.options.cssSelectors.timezone + ' ' + this.options.cssSelectors.timezone + '--note"> ' + this.options.translations.localTime + ' ' + gmtHolder + '</span>';

            holder.find('.' + this.options.cssSelectors.timezone + '--time').html(time + ' ' + noteContainer);
            holder.find('.' + this.options.cssSelectors.timezone + '--date').html(day);
        },
        rewriteUrlWithUtcOffset: function (url) {
            var sep = (url.indexOf('?') != -1) ? '&' : '?';
            return url + sep + 'utcoffset=' + encodeURIComponent(this.gmtOffset);
        },
        reloadSchedules: function (content) {
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
        rewriteScheduleLinks: function (content) {
            if (!this.gmtOffset) {
                return;
            }
            var scheduleLinks = (content) ? content.find(this.options.cssSelectors.offsetRewrite) : $(this.options.cssSelectors.offsetRewrite);
            var _this = this;
            scheduleLinks.each(function () {
                var $this = $(this);
                var href = $this.attr('href');
                if (href) {
                    // append ?utcoffset=xx:yy to any relevant URLs to prevent a double load
                    $this.attr('href', _this.rewriteUrlWithUtcOffset(href));
                    $this.removeAttr(_this.options.attrTargetAddUtcOffset);
                }
            });
        }
    };
    return TimezoneNotification;
});
