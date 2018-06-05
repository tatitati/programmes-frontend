define(['jquery', 'lazyload', 'istats-tracking', 'lazysizes'],function($, Lazyload, IstatsTracking, lazysizes){
    $(function() {
        // Lazy sizes (as of v4.0.2) breaks in IE11 without this hack
        window.lazySizes = lazysizes;
        // Load responsive image polyfill if needed
        var image = document.createElement( "img" );
        if (!("srcset" in image) || !("sizes" in image) || !(window.HTMLPictureElement)) {
            require(['picturefill'], function (picturefill) {})
        }

        var responsiveLazyload = new Lazyload();
        responsiveLazyload.init();

        var tracking = new IstatsTracking();
        tracking.init();
        $('body').on('lazyloadComplete', function(e, context) {
            if (context && context.content) {
                tracking.trackLinks(context.content);
            }
        });
    });

    return {
        $: $,
        Lazyload: Lazyload
    }
});
