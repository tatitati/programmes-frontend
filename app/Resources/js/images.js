define(['jquery', 'jquery.appear'], function ($) {
    var Images = function (options) {
        this.options = $.extend({}, this.options, options);
    };

    Images.prototype = {
        options : {
            context : $('body'),
            lazy_img_css : 'image--lazy',
            valid_divdata_attributes: ["data-src", "data-srcset", "data-sizes", "data-alt", "data-img-blank"],
            appear : {
                onscroll : true,
                Y: 200
            }
        },
        init: function () {
            if (this.options.appear.onscroll) {
                this.addOnScrollHandler(this.options.context);
            }
        },
        hasImgTag: function (element) {
            return (element.tagName.toLowerCase() === 'img');
        },
        convertDivToImage: function (div) {
            var divAttributes = (div.attributes) ? div.attributes : $(div)[0].attributes;

            var divAttribute;
            var newImg = document.createElement("img");

            for (var i = 0; (divAttribute = divAttributes[i]) != null; i++) {
                var attrName = divAttribute.name;
                var attrValue = divAttribute.value;

                if ($.inArray(attrName, this.options.valid_divdata_attributes) != -1) {
                    attrName = attrName.replace("data-", "")

                }

                newImg.setAttribute(attrName, attrValue);
            }

            return newImg;
        },
        switchDiv2Img: function(div) {
            var img = this.convertDivToImage(div);

            if (this.options.appear.onscroll) {
                $(img).addClass(this.options.lazy_img_css);
            }

            $(div).replaceWith($(img));

            return img;
        },
        addOnScrollHandler : function () {
            var lazyImages = $('.' + this.options.lazy_img_css, this.options.context);

            var self = this;

            // if current image is in viewport, show it
            lazyImages.appear(function() {
                var imageAppearing = this;
                self.displayImageOnViewport(imageAppearing);
            });

            // if the image is close the viewport, display it
            $(window).on('scroll', function() {
                self.anticipateToViewport();
            });
        },
        displayImageOnViewport : function (img) {
            if (!this.hasImgTag(img)) {
                 img = this.switchDiv2Img(img);
            }

            this.showImage(img);
        },
        showImage: function(img) {
            var self = this;
            $(img).bind('load', function() {
                $(img).removeClass(self.options.lazy_img_css);
            });
        },
        anticipateToViewport: function() {
            var hiddenImages = $('.' + this.options.lazy_img_css, this.options.context);

            var self = this;
            hiddenImages.each(function(index, element){
                if (self.isElementNearViewport(element)) {
                    self.displayImageOnViewport(element);
                }
            });
        },
        /**
         * We don't want to wait until an element is in view to start loading it
         * as then the user shall see a loading boxes. So instead start loading
         * the image just before it comes into view.
         */
        isElementNearViewport: function(element) {
            var $element = $(element);
            var offset = $element.offset();
            var top = offset.top;
            var $window = $(window);
            var window_top = $window.scrollTop();
            return top + $element.height() >= window_top && top - (200 || 0) <= window_top + $window.height();
        }

    };

    return Images;
});
