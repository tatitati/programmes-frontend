/**
 * DsAmen JS Bootstrap
 */
define(['eqjs', 'respimg', 'lazysizes'], function(EQ) {
        function init() {
            var selector = '[data-eq-pts]';
            EQ.query(document.querySelectorAll(selector));

            // refire EQ when a lazy load is complete
            window.addEventListener('lazyload-complete',function(e){
                EQ.query(document.querySelectorAll(selector));
            },false);
        }

        // cut the mustard
        if ('querySelector' in document
            && 'addEventListener' in window
        ) {
            init();
        }
    }
);
