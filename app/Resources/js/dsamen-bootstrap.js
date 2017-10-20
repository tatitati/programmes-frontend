/**
 * DsAmen JS Bootstrap
 */
define(['eqjs', 'lazyload', 'respimg', 'lazysizes'], function(EQ, Lazyload) {
        function init() {
            var selector = '[data-eq-pts]';
            EQ.query(document.querySelectorAll(selector));

            // refire EQ when a lazy load is complete
            window.addEventListener('lazyload-complete',function(e){
                EQ.query(document.querySelectorAll(selector));
            },false);

            var lazyload = new Lazyload();
            lazyload.init();
        }

        // cut the mustard
        if ('querySelector' in document
            && 'addEventListener' in window
        ) {
            init();
        }
    }
);
