define(['jquery', 'lazyload', 'istats-tracking', 'respimg', 'lazysizes'],function($, Lazyload, IstatsTracking){
    $(function() {
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
