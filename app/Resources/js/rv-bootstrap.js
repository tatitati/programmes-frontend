define(['jquery', 'lazyload', 'respimg', 'lazysizes'],function($, Lazyload){
    $(function() {
        var responsiveLazyload = new Lazyload();
        responsiveLazyload.init();
    });

    return {
        $: $,
        Lazyload: Lazyload
    }
});
