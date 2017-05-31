/*
This module lazy load images and convert them in case that they are <div> elements
to the proper image. This lazy-loading is triggered by the scroll.
 */
define(['jquery', 'jquery.appear'], function ($) {

    var Images = function (options) {
        this.setOptions(options);
    };

    Images.prototype = {
        options : {
            context : $('body'),
            data_img_src : 'data-img-src-',
            data_srcset: 'data-srcset',
            data_blank_img : 'data-img-blank',
            lazy_img_css : 'image--lazy',
            valid_img_attributes : ['class', 'id', 'title', 'name'],
            appear : {
                onscroll : true,
                Y: 200
            }
        },
        setOptions : function (options) {
            this.options = $.extend({}, this.options, options);
        },
        init : function () {
            var context = (context) ? context : this.options.context;

            if (this.options.appear.onscroll) {
                this.addOnScrollHandler(context);
            }
        },
        hasImgTag : function (element) {
            return (element.tagName.toLowerCase() === 'img');
        },

        convertDivToImage : function (div) {
            var divAttributes = (div.attributes) ? div.attributes : $(div)[0].attributes;

            var divAttribute;
            var img = document.createElement("img");

            for (var i = 0; (divAttribute = divAttributes[i]) != null; i++) {
                attrName = divAttribute.name;
                attrValue = divAttribute.value;

                if (this.isValidImgAttribute(divAttribute.name) && divAttribute.value) {
                    img.setAttribute(attrName, attrValue);
                } else {
                    switch (attrName) {
                        case 'data-srcset':
                            img.srcset = attrValue;
                            break;
                        case 'data-sizes':
                            img.sizes = attrValue;
                            break;
                        case 'data-alt':
                            img.alt = attrValue;
                            break;
                        default:
                            break;
                    }
                }
            }

            img.src = this.getImgAttrFromDiv(div);

            return img;
        },

        isValidImgAttribute : function (attr) {
            return ($.inArray(attr, this.options.valid_img_attributes) !== -1);
        },

        getImgAttrFromDiv: function(div){
            // by default we set the src attr to the first srcset url.
            var srcsets = div.getAttribute(this.options.data_srcset);

            var url;
            if (srcsets) {
                var parts = srcsets[0].split(' ');
                url = parts[0];
            }

            return (url ? url : div.getAttribute(this.options.data_blank_img));
        },

        switchDiv2Img: function(div) {
            var img = this.convertDivToImage(div);

            if (this.options.appear.onscroll) {
                $(img).addClass(this.options.lazy_img_css);
            }

            $(div).replaceWith($(img));

            return img;
        },
        addOnScrollHandler : function (context) {
            var _this = this;

            // if current image is in viewport, show it
            $('.' + this.options.lazy_img_css, context).appear(function() {
                _this.displayImageOnViewport(this);
            });

            // if the image is close the viewport, display it
            $(window).on('scroll', function() {
                _this.anticipateToViewport();
            });
        },
        displayImageOnViewport : function (img) {
            var _this = this;
            if (!this.hasImgTag(img)) {
                 img = _this.switchDiv2Img(img);
            }

            // display converted image
            _this.showImage(img);
        },
        showImage: function(img) {
            var _this = this;
            $(img).bind('load', function() {
                $(img).removeClass(_this.options.lazy_img_css).unbind('load');
            });
        },
        anticipateToViewport: function(context) {
            var _this = this;
            var hiddenImages = $('.' + this.options.lazy_img_css, context);

            hiddenImages.each(function(index, element){
                if (_this.isElementNearViewport(element)) {
                    _this.displayImageOnViewport(element);
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
