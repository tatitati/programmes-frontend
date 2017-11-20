/**
 * DsAmen JS Bootstrap
 */
define(['eqjs', 'lazyload', 'istats-tracking', 'jquery-1.9', 'respimg', 'lazysizes'], function(EQ, Lazyload, IstatsTracking, $) {
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
    }
);
