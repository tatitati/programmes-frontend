define(['jquery', 'images'],function($, Images){
    $(function() {
        var responsiveImages = new Images();
        responsiveImages.init();
    });

    return {
        $: $,
        Images: Images
    }
});
