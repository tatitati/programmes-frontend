/**
 * DsAmen JS Bootstrap
 */
define(['eqjs', 'lazyload', 'istats-tracking', 'jquery-1.9', 'lazysizes'], function(EQ, Lazyload, IstatsTracking, $, lazysizes) {

        function init() {
            var selector = '[data-eq-pts]';
            EQ.query(document.querySelectorAll(selector));

            // refire EQ when a lazy load is complete
            window.addEventListener('lazyload-complete',function(e){
                EQ.query(document.querySelectorAll(selector));
            },false);
            var lazyload = new Lazyload();
            lazyload.init();

            var tracking = new IstatsTracking();
            tracking.init();
            $('body').on('lazyloadComplete', function(e, context) {
                if (context && context.content) {
                    tracking.trackLinks(context.content);
                }
            });
        }

        // cut the mustard
        if ('querySelector' in document
            && 'addEventListener' in window
        ) {
            init();
        }

        // Lazy sizes (as of v4.0.2) breaks in IE11 without this hack
        window.lazySizes = lazysizes;
        // Load responsive image polyfill if needed
        var image = document.createElement( "img" );
        if (!("srcset" in image) || !("sizes" in image) || !(window.HTMLPictureElement)) {
            require(['picturefill'], function (picturefill) {})
        }
    }
);
