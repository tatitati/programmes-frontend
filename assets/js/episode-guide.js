define(['jquery-1.9'], function ($) {
    "use strict";

    (function() {
        var classDropdownLink = '.js-serieslink',
            classAnyItemInGuide = '.js-guideitem',
            classDropDownWrapperBox = 'dropdown-symbol',
            geliconClass = 'dropdown-icon',
            geliconClassExpanded = geliconClass + '--expanded';

        /**
         * "Serie links" work to load and expand the serie, displaying all the epidoses for the clicked series.
         */
        var findDropdownLinkInsideSeries = function(series) {
            return series.find(classDropdownLink);
        };

        var isSeriesExpanded = function(series)
        {
            return series.hasClass('js-active');
        };

        var isSeriesContentAlreadyLoaded = function(series)
        {
            return series.attr('data-fetched') == 'true';
        };

        var isSeriesContentLoadingNow = function(series)
        {
            return series.hasClass('js-inprogress');
        };

        /**
         * We dont want to display any dropdown symbol when the user has javascript disabled. In this case
         * there will not be any collapsable/expandible action attached.
         */
        var showDropdownButtons = function()
        {
            $("." + classDropDownWrapperBox).removeClass(classDropDownWrapperBox + '--hidden');
        };

        var toggleDropDown = function(series)
        {
            if (isSeriesExpanded(series)) {
                collapseSeriesSelected(series);
            } else {
                expandSeriesSelected(series);
            }
        };

        var openSeriesIfSeriesInFirstPosition = function()
        {
            // special requirement: in case that the first element is a
            // series, we open it automatically
            var firstItem = $(classAnyItemInGuide).eq(0);
            if (firstItem.length > 0 && firstItem.is('[data-incpath]')) {
                toggleDropDown(firstItem);
            }
        };

        /**
         * One series can be collapsed (icon: >) or expanded (icon: V)
         *
         * @Example 'V SEASON 1'
         */
        var showExpandedIcon = function(seriesLinkClicked)
        {
            showDropdownButtons();
            var dropdown = seriesLinkClicked.find("." + geliconClass).eq(0);
            var currentClasses = dropdown.attr('class');

            if (!currentClasses.match('/'+ geliconClassExpanded + '/')) {
                var newClasses = currentClasses + " " + geliconClassExpanded;
                dropdown.attr('class', newClasses);
            }
        };

        /**
         * One series can be collapsed (icon: >) or expanded (icon: V)
         *
         * @Example '> SEASON 1'
         */
        var showCollapsedIcon = function(seriesLinkClicked)
        {
            var dropdown = seriesLinkClicked.find("." + geliconClass).eq(0);
            var currentClasses = dropdown.attr('class');

            var newClasses = currentClasses.replace(geliconClassExpanded, '');
            dropdown.attr('class', newClasses);
        };

        /**
         * We want to show the content of a selected series, so initially we have to load
         * the content of it to later display it. In case that the content is already loaded,
         * we go through the happy way (just display it)
         */
        var expandSeriesSelected = function(series) {
            var seriesLinkClicked = series.find(classDropdownLink),
                panel = seriesLinkClicked.find('.panel').eq(0);

            if (!isSeriesContentLoadingNow(series) && !isSeriesContentAlreadyLoaded(series)) {
                return loadEpisodesFromSelectedSeries(series, true);
            }

            // expand series: replace "class" value for the classes stored in the data variable "activeclass"
            series.attr('class', series.attr('data-activeclass'));
            showExpandedIcon(seriesLinkClicked);

            // accessibility
            seriesLinkClicked.attr('aria-expanded','true');
        };

        /**
         * Oposite to expandSeriesSelected().
         */
        var collapseSeriesSelected = function(series) {
            var seriesLinkClicked = series.find(classDropdownLink),
                panel = $('#' + seriesLinkClicked.attr('aria-controls'));

            // collapse series: replace "class" value for the classes stored in the data variable "activeclass"
            series.attr('class', series.attr('data-inactiveclass'));
            showCollapsedIcon(seriesLinkClicked);

            // accessibility
            seriesLinkClicked.attr('aria-expanded','false');
        };

        /**
         * Load content (list of episodes or nested series) for the selected series.
         */
        var loadEpisodesFromSelectedSeries = function(series, shouldRedirectIfFail) {
            var url=series.attr('data-incpath');

            $.ajax({
                url: url,
                beforeSend: function() {
                    series.addClass('js-inprogress');
                },
                success: function (data) {
                    series.find('.js-seriescontent').html(data);
                    series.attr('data-fetched', 'true');

                    // series can have nested series, so we have to re-subscribe
                    // the new loaded elements to click event because
                    // this new content will be able to collapse/expand
                    attachClickEventTo(classDropdownLink);
                    expandSeriesSelected(series);
                },
                error: function () {
                    /* Any errors, just bounce to the destination page */
                    if (shouldRedirectIfFail) {
                        window.location.href = findDropdownLinkInsideSeries(series).attr('href');
                    } else {
                        collapseSeriesSelected(series);
                    }
                }
            })
        };

        /**
         * When loading series content we can have nested collapsible elements (nested series).
         * This series needs to be also expandable/collapsible, showing the whole tree. Cause of
         * this we have to re-attach click to the new elements that little by little we add to the
         * tree displayed.
         *
         * @example
         *      SERIES 1
         *          EPISODE S1E1
         *          EPISODE S1E2
         *          SERIES  S1S1
         *              EPISODE S1S1E1
         *              EPISODE S1S1E2
         *              ....
         *      SERIES B
         *              ....
         */
        var attachClickEventTo = function(linksSeriesSelector) {
            $(linksSeriesSelector).off("click").click(function(event) {
                event.preventDefault();
                event.stopPropagation();

                var series = $(this).closest(classAnyItemInGuide);
                toggleDropDown(series)
            });
        };

        showDropdownButtons();
        attachClickEventTo(classDropdownLink);
        openSeriesIfSeriesInFirstPosition();
    }());
});
